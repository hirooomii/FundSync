import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

export async function parsePDF(file) {
  const arrayBuffer = await file.arrayBuffer();
  const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

  let fullText = '';
  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const page = await pdf.getPage(pageNum);
    const content = await page.getTextContent();
    const strings = content.items.map(item => item.str.trim());
    fullText += strings.join(',') + ',';
  }

  return fullText;
}

export async function parseBPIStatement(text) {

  const accountMatch = text.match(/Account Number:,,(\d+)/);
  const account = accountMatch ? accountMatch[1] : null;

  const dateRangeMatch = text.match(/Date Range From (\d{2}\/\d{2}\/\d{4}) To (\d{2}\/\d{2}\/\d{4})/);
  const beg_date = dateRangeMatch ? dateRangeMatch[1] : null;
  const end_date = dateRangeMatch ? dateRangeMatch[2] : null;

  const transactionRegex = /(\d{2}\/\d{2}\/\d{4})(?:,,)?([\dA-Z]{4})?,?([\dA-Z]{4})?,?([\w\s]+),?([\w\s,]+),?([\d,]+\.\d+)?[^,]*,,([\d,]+\.\d+)/g;

  const transactions = [];
  let match;

  while ((match = transactionRegex.exec(text)) !== null) {
    const [
      _full,
      date,
      firstCode,
      secondCode,
      desc1,
      desc2,
      amountRaw,
      runningRaw
    ] = match;


    let transaction_code = firstCode;  
    if (secondCode?.match(/^\d{4}$/)) {
      transaction_code = secondCode;  
    }

    let description = `${desc1 ?? ''} ${desc2 ?? ''}`.trim().replace(/\s+/g, ' ');

    description = description.replace(/\d{4,}/g, '').trim();  

    if (!description) {
      description = 'N/A';
    }

    const amountMatch = /([\d,]+)\s*$/.exec(description);
    let amount = 0;
    if (amountMatch) {
      amount = parseFloat(amountMatch[1].replace(/,/g, ''));
      description = description.replace(amountMatch[0], '').trim();
    }

    const running_balance = parseFloat(runningRaw.replace(/,/g, ''));

    transactions.push({
      date,
      transaction_code,
      description,
      amount,
      running_balance
    });
  }

  const beginning_balance = transactions.length > 0 ? transactions[transactions.length - 1].running_balance : null;
  const ending_balance = transactions.length > 0 ? transactions[0].running_balance : null;

  return {
    account,
    beg_date,
    end_date,
    beginning_balance,
    ending_balance,
    transactions
  };
}
