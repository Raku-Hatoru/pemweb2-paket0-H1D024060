<?php

namespace App;

use App\Models\Borrowing;
use Illuminate\Support\Collection;

class BorrowingReportPdf
{
    /**
     * @param  array<string, int>  $summary
     * @param  Collection<int, Borrowing>  $borrowings
     */
    public function build(string $title, string $periodLabel, array $summary, Collection $borrowings): string
    {
        $lines = [
            $title,
            'Periode: '.$periodLabel,
            '',
            'Ringkasan',
            'Total transaksi: '.$summary['total_transactions'],
            'Total buku dipinjam: '.$summary['total_books'],
            'Total transaksi selesai: '.$summary['returned_transactions'],
            'Total transaksi aktif: '.$summary['active_transactions'],
            'Total denda: Rp '.number_format($summary['total_fine'], thousands_separator: '.'),
            '',
            'Detail transaksi',
        ];

        foreach ($borrowings as $borrowing) {
            $lines[] = sprintf(
                '%s | %s | %s | %s | %d buku | Rp %s',
                $borrowing->borrow_date->format('d-m-Y'),
                $borrowing->member->member_code,
                $this->sanitize($borrowing->member->user->name),
                $borrowing->status->value,
                (int) $borrowing->total_books,
                number_format($borrowing->total_fine, thousands_separator: '.')
            );
        }

        return $this->renderPdf($title, $lines);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function renderPdf(string $title, array $lines): string
    {
        $chunks = array_chunk($lines, 38);
        $pageCount = count($chunks);
        $catalogId = 1;
        $pagesId = 2;
        $firstPageId = 3;
        $fontId = $firstPageId + ($pageCount * 2);
        $objects = [];
        $pageIds = [];

        foreach ($chunks as $index => $chunk) {
            $pageId = $firstPageId + ($index * 2);
            $contentId = $pageId + 1;
            $pageIds[] = $pageId;

            $streamLines = ['BT'];
            $streamLines[] = '/F1 16 Tf';
            $streamLines[] = sprintf('1 0 0 1 50 800 Tm (%s) Tj', $this->escapeForPdf($title));
            $streamLines[] = '/F1 10 Tf';

            $y = 780;
            foreach ($chunk as $line) {
                $streamLines[] = sprintf('1 0 0 1 50 %d Tm (%s) Tj', $y, $this->escapeForPdf($line));
                $y -= 18;
            }

            $streamLines[] = 'ET';

            $stream = implode("\n", $streamLines);

            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 %d 0 R >> >> /Contents %d 0 R >>',
                $pagesId,
                $fontId,
                $contentId
            );
            $objects[$contentId] = sprintf(
                "<< /Length %d >>\nstream\n%s\nendstream",
                strlen($stream),
                $stream
            );
        }

        $objects[$catalogId] = sprintf('<< /Type /Catalog /Pages %d 0 R >>', $pagesId);
        $objects[$pagesId] = sprintf(
            '<< /Type /Pages /Count %d /Kids [%s] >>',
            $pageCount,
            implode(' ', array_map(fn (int $id): string => "{$id} 0 R", $pageIds))
        );
        $objects[$fontId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $objectId => $objectBody) {
            $offsets[$objectId] = strlen($pdf);
            $pdf .= "{$objectId} 0 obj\n{$objectBody}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $objectCount = max(array_keys($objects));

        $pdf .= "xref\n";
        $pdf .= '0 '.($objectCount + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($objectId = 1; $objectId <= $objectCount; $objectId++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$objectId] ?? 0)."\n";
        }

        $pdf .= "trailer\n";
        $pdf .= sprintf("<< /Size %d /Root %d 0 R >>\n", $objectCount + 1, $catalogId);
        $pdf .= "startxref\n";
        $pdf .= $xrefOffset."\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    private function sanitize(string $text): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return $normalized === false ? $text : $normalized;
    }

    private function escapeForPdf(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\(', '\)'],
            $this->sanitize($text)
        );
    }
}
