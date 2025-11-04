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

export async function parseAgriStatement(rawText) {
    const text = rawText.replace(/\r\n/g, '').replace(/\n/g, '').replace(/\s+/g, ' ').trim();

    const accountMatch = text.match(/Account ID,,(\d+)/);
    const account = accountMatch ? accountMatch[1] : null;

    const begDateMatch = text.match(/From\s+(\d{4}-\d{2}-\d{2})/);
    const endDateMatch = text.match(/to\s+(\d{4}-\d{2}-\d{2})/);
    const beg_date = begDateMatch ? begDateMatch[1].replace(/-/g, '/') : null;
    const end_date = endDateMatch ? endDateMatch[1].replace(/-/g, '/') : null;

    const getFieldValue = (key) => {
        const match = text.match(new RegExp(`${key},,([\\d,\\.]+)`));
        return match ? parseFloat(match[1].replace(/,/g, '')) : null;
    };

    const beginning_balance = getFieldValue("Beginning Balance");
    const ending_balance = getFieldValue("Ending Balance");

    const rawTransactions = text.split(/(?=\d{2}-[A-Za-z]{3}-\d{4},,)/g);

    const transactions = [];

    for (const chunk of rawTransactions) {
        if (!chunk.match(/^\d{2}-[A-Za-z]{3}-\d{4},,/)) continue;

        const parts = chunk.split(',,').map(s => s.trim()).filter(Boolean);

        if (parts.length < 4) continue;

        const rawDate = parts[0];
        const date = parseDateFromDmyText(rawDate);

        const running_balance_str = parts[parts.length - 1];
        const amount_str = parts[parts.length - 2];
        const description = parts.slice(1, parts.length - 2).join(' ');

        const running_balance = parseFloat(running_balance_str.replace(/,/g, ''));
        const amount = parseFloat(amount_str.replace(/,/g, ''));

        if (date && !isNaN(amount) && !isNaN(running_balance)) {
            transactions.push({
                date,
                description,
                amount,
                running_balance
            });
        }
    }

    return {
        account,
        beginning_balance,
        ending_balance,
        beg_date,
        end_date,
        transactions
    };
}