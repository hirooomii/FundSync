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

export async function parsePBCOMStatement(text) {
    
        const accountMatch = text.match(/Account Number:\s*(\d+)/);
        const account = accountMatch ? accountMatch[1] : null;

        const lines = text.split(/\n|(?=\d{4}\/\d{2}\/\d{2}\s+\d{2}:\d{2})/g);
        const transactions = [];

        for (const line of lines) {
            const match = line.match(/^(\d{4}\/\d{2}\/\d{2})\s+\d{2}:\d{2}.*?,,(.*?),,([\d,]+\.\d{2}).*?,,([\d,]+\.\d{2})/);

            if (match) {
                const [, date, descriptionRaw, amountRaw, balanceRaw] = match;
                const description = descriptionRaw.replace(/,+$/, '').trim(); 
                const amount = parseFloat(amountRaw.replace(/,/g, ''));
                const running_balance = parseFloat(balanceRaw.replace(/,/g, ''));

                transactions.push({
                    date,
                    description,
                    amount,
                    running_balance
                });
            }
        }

        const beg_date = transactions.length ? transactions[0].date : null;
        const end_date = transactions.length ? transactions[transactions.length - 1].date : null;
        const beginning_balance = transactions.length ? transactions[0].running_balance : null;
        const ending_balance = transactions.length ? transactions[transactions.length - 1].running_balance : null;

        return {
            account,
            beginning_balance,
            ending_balance,
            beg_date,
            end_date,
            transactions
        };
}
