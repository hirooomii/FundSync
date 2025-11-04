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

export async function parseUBStatement(text) {
    const accountMatch = text.match(/Account Number,,([\d ]+)/);
    const account = accountMatch ? accountMatch[1].replace(/\s/g, '') : null;

    const periodMatch = text.match(/Period Covered,,(\d{4}-\d{2}-\d{2}) - (\d{4}-\d{2}-\d{2})/);
    const beg_date = periodMatch ? periodMatch[1] : null;
    const end_date = periodMatch ? periodMatch[2] : null;

    const lines = text.split(/\n|(?=\d{4}\/\d{2}\/\d{2},)/).filter(line => /^\d{4}\/\d{2}\/\d{2},/.test(line));

    const parseAmount = str => {
        if (!str || str === '—' || str === '-' || str === '---') return null;
        return parseFloat(str.replace(/,/g, ''));
    };

   const transactions = lines.reduce((acc, line) => {
        if (line.includes('PAGE')) {
            line = line.split(/PAGE/i)[0].trim();
        }

        const parts = line.split(',,').map(p => p.trim());
        const date = parts[0];

        const running_balance = parseAmount(parts[parts.length - 2]);
        if (isNaN(running_balance)) return acc; 

        const credit = parseAmount(parts[parts.length - 3]);
        const debit = parseAmount(parts[parts.length - 4]);
        const check_reference = parts[parts.length - 5] || '---';
        const transaction = parts[2];

        const descStart = 3;
        const descEnd = parts.length - 5;
        const description = parts.slice(descStart, descEnd).join(' ').replace(/\s+/g, ' ').trim();

        acc.push({
            date,
            transaction,
            description,
            check_reference,
            debit,
            credit,
            running_balance
        });

        return acc;
    }, []);

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
