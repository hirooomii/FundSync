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

export async function parseAUBStatement(text) {
     const accountMatch = text.match(/Account Number\s*:\s*([\d\-]+)/);
        const account = accountMatch ? accountMatch[1].replace(/-/g, '') : null;

        const rows = text
            .split(',,')
            .join('|')
            .split(/(?=\d{2}\/\d{2}\/\d{4}\|)/)
            .map(row => row.trim())
            .filter(row => /^\d{2}\/\d{2}\/\d{4}/.test(row));

        const transactions = [];
        let beginning_balance = null;
        let ending_balance = null;

        rows.forEach(row => {
            const parts = row.split('|').map(p => p.trim());

            const [date, check_no, description, debit, credit, balance] = [
                parts[0] || '',
                parts[1] || '',
                parts[2] || '',
                parts[3]?.replace(/,/g, '') || '',
                parts[4]?.replace(/,/g, '') || '',
                parts[5]?.replace(/,/g, '') || ''
            ];

            const cleanedAmount = parseFloat(description.replace(/,/g, '').replace(/[^\d.]/g, ''));

            if (check_no === 'BEGINNING BALANCE') {
                beginning_balance = {
                    date,
                    amount: cleanedAmount || 0
                };
            } else if (check_no === 'ENDING BALANCE') {
                ending_balance = {
                    date,
                    amount: cleanedAmount || 0
                };
            } else {
                transactions.push({
                    date,
                    check_no,
                    transaction_description: description,
                    debit,
                    credit,
                    balance
                });
            }
        });

        return {
            account,
            beginning_balance,
            ending_balance,
            transactions
        };
}
