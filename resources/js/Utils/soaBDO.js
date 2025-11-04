import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;


// 🔹 PDF to TEXT
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

// 🔹 FOR BDO PARSER
export async function parseBPOStatement(text) {
  const accountMatch = text.match(/Account Number:.*?,.*?,(\d+)/);
  const account = accountMatch ? accountMatch[1] : null;

  const dateRangeMatch = text.match(/Period Covered:.*?,.*?,(\d{2}\/\d{2}\/\d{4}) To (\d{2}\/\d{2}\/\d{4})/);
  const beg_date = dateRangeMatch ? dateRangeMatch[1] : null;
  const end_date = dateRangeMatch ? dateRangeMatch[2] : null;

  const transactionLines = text.split('\n').join('').split(/(?=\d{2}\/\d{2}\/\d{4},)/g);
  const transactions = [];

  for (let line of transactionLines) {
    const dateMatch = line.match(/^(\d{2}\/\d{2}\/\d{4})/);
    if (!dateMatch) continue;

    const date = dateMatch[1];
    const parts = line.split(',');

    const possibleCheckRef = parts.find(p => /\b\d{9}\b/.test(p));
    const check_reference = possibleCheckRef ? possibleCheckRef.match(/\b\d{9}\b/)[0] : null;

    let amount = null;
    let running_balance = null;

    const bundle_name = parts.slice(1, parts.length - 3).join(',').trim();
    const amountMatch = line.match(/,([\d,]+\.\d{2}),,[\d,]+\.\d{2}/);
    amount = amountMatch ? parseFloat(amountMatch[1].replace(/,/g, '')) : null;

    const balanceMatch = line.match(/,,([\d,]+\.\d{2})\s+\d{9}/);
    running_balance = balanceMatch ? parseFloat(balanceMatch[1].replace(/,/g, '')) : null;

    if (running_balance !== null) {
      transactions.push({
        date,
        bundle_name,
        amount,
        running_balance,
        check_reference,
      });
    }
  }

  return {
    account,
    beginning_balance: transactions.length ? transactions[0].running_balance : null,
    ending_balance: transactions.length ? transactions[transactions.length - 1].running_balance : null,
    beg_date,
    end_date,
    transactions,
  };
}

