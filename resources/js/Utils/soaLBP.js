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

export async function parseLBPStatement(text) {

        const result = {
            account: null,
            beginning_balance: null,
            ending_balance: null,
            beg_date: null,
            end_date: null,
            transactions: []
        };

        const accountMatch = text.match(/Account,,No.,,([\d-]+)/);
        if (accountMatch) {
            result.account = accountMatch[1].replace(/-/g, '');
        }

        const amountsAndBalances = [...text.matchAll(/([\d,]+\.\d{2}),,([\d,]+\.\d{2})/g)].map(m => ({
            amount: parseFloat(m[1].replace(/,/g, '')),
            running_balance: parseFloat(m[2].replace(/,/g, ''))
        }));

        const transactionRegex = /(\d{2}\/\d{2}\/\d{4}),\d{2}:\d{2}:\d{2},,(.*?),(?=\d{2}\/\d{2}\/\d{4}|$)/gs;

        let match, i = 0;
        while ((match = transactionRegex.exec(text)) !== null && i < amountsAndBalances.length) {
            const date = match[1];
            const line = `${date},${match[2]}`.trim();

            const all10DigitNumbers = [...line.matchAll(/\b\d{10}\b/g)].map(m => m[0]);
            const check_reference = all10DigitNumbers.length > 0
                ? all10DigitNumbers[all10DigitNumbers.length - 1]
                : '00000000';

              const parts = line.split(',,');
                let description = '';
                let branch = '';

                if (parts.length >= 2) {
                    const descParts = parts[0].split(',');
                    if (descParts.length > 1) {
                        description = descParts.slice(1).join(',').trim().replace(/,+$/, '');
                    } else {
                        description = parts[0].trim().replace(/,+$/, '');
                    }
                    branch = parts[1].split(',')[0].trim();
                }


            const { amount, running_balance } = amountsAndBalances[i++];

            result.transactions.push({
                date,
                description,
                branch,
                amount,
                running_balance,
                check_reference
            });
    }


        if (result.transactions.length > 0) {
            result.ending_balance = result.transactions[0].running_balance;
            result.beginning_balance = result.transactions[result.transactions.length - 1].running_balance;
            result.beg_date = result.transactions[0].date;
            result.end_date = result.transactions[result.transactions.length - 1].date;
        }

        return result;
}
