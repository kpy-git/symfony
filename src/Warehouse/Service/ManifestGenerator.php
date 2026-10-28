<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\ValueObject\ShipmentManifest;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class ManifestGenerator
{
    public function __construct(
        private Environment $twig,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    )
    {
    }

    /**
     * @throws KpyException
     */
    public function generateManifest(array $shipments, \DateTimeImmutable $collectionDate): string
    {
        if (empty($shipments)) {
            throw new KpyException('No shipments found for manifest generation');
        }

        $logoPath = $this->projectDir . '/public/images/carrier/mrw.jpg';

        if (!is_readable($logoPath)) {
            throw new \RuntimeException('No se puede leer el logo de MRW, ' . $this->projectDir . '/public/images/carrier/mrw.jpg');
        }

        $totalPackages = 0;
        $totalWeight = 0;

        /** @var ShipmentManifest $shipment */
        foreach ($shipments as $shipment) {
            $totalPackages += $shipment->getPackagesCount();
            $totalWeight += (float)$shipment->getWeight();
        }

        try {
            $html = $this->twig->render('warehouse/manifest.pdf.html.twig', [
                'logoPath' => $logoPath,
                'franchiseCode' => '02100',
                'franchiseName' => 'JAEN',
                'subscriber' => '002006-PREMIER PET SOLUTIONS, SL',
                'collectionDate' => $collectionDate,
                'shipments' => $shipments,
                'totalPackages' => $totalPackages,
                'totalWeight' => $totalWeight,
                'printedAt' => new \DateTimeImmutable(),
            ]);

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->setChroot($this->projectDir . '/public/images');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
            $size = 8;

            $canvas->page_text(
                $canvas->get_width() - 90,
                $canvas->get_height() - 25,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $font,
                $size
            );

            return $dompdf->output();

        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new KpyException("Error al generar el manifiesto:\n" . $e->getMessage());
        }

    }
}
