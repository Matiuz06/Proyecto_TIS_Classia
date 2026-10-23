<?php

/**
 * Responsabilidad: genera el PDF fijo de certificados Classia con FPDF.
 */
class CertificadoPdf
{
    public function generar(array $datos): void
    {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetTitle($this->txt('Certificado Classia'));
        $pdf->SetAuthor($this->txt('Classia'));
        $pdf->AddPage();

        // Marco exterior
        $pdf->SetDrawColor(42, 79, 145);
        $pdf->SetLineWidth(1.2);
        $pdf->Rect(12, 12, 273, 186);

        // Marco interior
        $pdf->SetDrawColor(160, 174, 192);
        $pdf->SetLineWidth(0.3);
        $pdf->Rect(18, 18, 261, 174);

        // Logo Classia
        $logoPath = __DIR__ . '/../../assets/images/logo-classia.png';

        if (file_exists($logoPath)) {
            $logoWidth = 65;
            $pageWidth = 297; // A4 horizontal en milímetros
            $logoX = ($pageWidth - $logoWidth) / 2;

            $pdf->Image(
                $logoPath,
                $logoX,
                18,
                $logoWidth
            );

            // Espacio después del logo
            $pdf->SetY(42);
        } else {
            // Fallback si el logo no existe
            $pdf->SetFont('Arial', 'B', 22);
            $pdf->SetTextColor(42, 79, 145);
            $pdf->Cell(0, 14, $this->txt('CLASSIA'), 0, 1, 'C');
            $pdf->Ln(8);
        }

        // Título
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(
            0,
            14,
            $this->txt('CERTIFICADO DE FINALIZACION'),
            0,
            1,
            'C'
        );

        // Texto introductorio
        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(
            0,
            8,
            $this->txt('Se certifica que'),
            0,
            1,
            'C'
        );

        // Nombre del alumno
        $pdf->SetFont(
            'Arial',
            'B',
            $this->fontSizeFor((string)$datos['alumno'], 28, 52)
        );

        $pdf->SetTextColor(15, 23, 42);

        $pdf->MultiCell(
            0,
            13,
            $this->txt((string)$datos['alumno']),
            0,
            'C'
        );

        // Texto intermedio
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(30, 41, 59);

        $pdf->Cell(
            0,
            8,
            $this->txt(
                'ha completado y aprobado satisfactoriamente el curso'
            ),
            0,
            1,
            'C'
        );

        // Nombre del curso
        $pdf->SetFont(
            'Arial',
            'B',
            $this->fontSizeFor((string)$datos['curso'], 20, 70)
        );

        $pdf->SetTextColor(42, 79, 145);

        $pdf->MultiCell(
            0,
            11,
            $this->txt((string)$datos['curso']),
            0,
            'C'
        );

        // Datos académicos
        $pdf->Ln(8);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetFont('Arial', '', 12);

        $pdf->Cell(
            0,
            8,
            $this->txt(
                'Porcentaje alcanzado: '
                . number_format(
                    (float)$datos['porcentaje'],
                    2,
                    ',',
                    '.'
                )
                . '%'
            ),
            0,
            1,
            'C'
        );

        $pdf->Cell(
            0,
            8,
            $this->txt(
                'Fecha de emision: '
                . date(
                    'd/m/Y',
                    strtotime((string)$datos['fecha'])
                )
            ),
            0,
            1,
            'C'
        );

        // Código de verificación
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(30, 41, 59);

        $pdf->Cell(
            0,
            7,
            $this->txt('Codigo de verificacion'),
            0,
            1,
            'C'
        );

        $pdf->SetFont('Arial', '', 13);

        $pdf->Cell(
            0,
            8,
            $this->txt((string)$datos['codigo']),
            0,
            1,
            'C'
        );

        // Pie del certificado
        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(100, 116, 139);

        $pdf->Cell(
            0,
            6,
            $this->txt(
                'Verificable publicamente en Classia mediante el codigo indicado.'
            ),
            0,
            1,
            'C'
        );

        // Nombre del archivo
        $archivo = (string)(
            $datos['archivo']
            ?? 'certificado-classia.pdf'
        );

        $pdf->Output(
            'I',
            $this->txt($archivo)
        );
    }

    /**
     * Convierte UTF-8 a Windows-1252 para las fuentes estándar de FPDF.
     */
    private function txt(string $texto): string
    {
        return iconv(
            'UTF-8',
            'windows-1252//TRANSLIT',
            $texto
        ) ?: $texto;
    }

    /**
     * Reduce el tamaño de fuente cuando el texto supera cierta longitud.
     */
    private function fontSizeFor(
        string $texto,
        int $base,
        int $limite
    ): int {
        $largo = mb_strlen($texto);

        if ($largo <= $limite) {
            return $base;
        }

        return max(
            14,
            $base - (int)ceil(($largo - $limite) / 5)
        );
    }
}