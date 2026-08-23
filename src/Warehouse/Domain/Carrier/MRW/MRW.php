<?php

namespace App\Warehouse\Domain\Carrier\MRW;

use App\Warehouse\Domain\Carrier\CarrierInterface;
use App\Warehouse\Domain\Exception\ShipmentException;
use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MRW implements CarrierInterface
{
    private string $logPath;

    public function __construct(
        private readonly string              $franquicia,
        private readonly string              $abonado,
        private readonly string              $user,
        private readonly string              $password,
        private readonly HttpClientInterface $client,
        private readonly SerializerInterface $serializer,
        private readonly Filesystem          $filesystem,
        private readonly bool                $debugCarrierRequest,
        #[Autowire('%env(MRW_SERVICIO)%')]
        readonly private string              $mrwServiceCode,
        #[Autowire('%kernel.logs_dir%')]
        string                               $logPath,
        #[Autowire('%env(MRW_TRACKING_URL)%')]
        readonly private string              $urlTrackingServices
    )
    {
        $this->logPath = $logPath . '/mrw/';
    }

    /**
     * @throws ShipmentException
     */
    public function createShipment(Order $order, int $parcels): Shipment
    {
        $recipient = new MRWRecipient();
        $recipient->fillWith($order->getCustomer());

        $body = $this->prepareRequestTransmEnvio($order, $recipient, $parcels);

        $response = $this->executeNewShipmentRequest($body);

        $cleanXml = $this->extractElementFromXml($response, 'TransmEnvioResult');

        $result = $this->serializer->deserialize($cleanXml, TransmEnvioResultDTO::class, 'xml');

        if ($result->Estado === 0) {
            throw new ShipmentException("Ha ocurrido un error al generar el envío:\n" .
                $result->Mensaje);
        }

        $bodyLabel = $this->prepareRequestLabels($result->NumeroEnvio);

        $labelsResponse = $this->executeGetLabelRequest($bodyLabel);

        $labelXml = $this->extractElementFromXml($labelsResponse, 'GetEtiquetaEnvioResult');
        $resultLabel = $this->serializer->deserialize($labelXml, GetEtiquetaEnvioResultDTO::class, 'xml');


        if ($resultLabel->Estado === 0) {
            throw new ShipmentException("Ha ocurrido un error al obtener la etiqueta del envío: \n" .
                $result->Mensaje);
        }

        return new Shipment(
            $order->getOrderId(),
            $resultLabel->EtiquetaFileZpl,
            $result->NumeroEnvio
        );

    }

    private function prepareRequestTransmEnvio(Order $order, MRWRecipient $recipient, int $parcels): string
    {
        $today = date('d/m/Y');
        $crm = $order->isCRM() ? '<mrw:Reembolso>O</mrw:Reembolso>' : '';
        $amount = $order->isCRM() ? '<mrw:ImporteReembolso>' . str_replace(".", ",", $order->getCrm()) . '</mrw:ImporteReembolso>' : '';

        return <<<XML
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:mrw="http://www.mrw.es/">
   <soap:Header>
      <mrw:AuthInfo>
         <mrw:CodigoFranquicia>{$this->franquicia}</mrw:CodigoFranquicia>
         <mrw:CodigoAbonado>{$this->abonado}</mrw:CodigoAbonado>
         <mrw:CodigoDepartamento/>
         <mrw:UserName>{$this->user}</mrw:UserName>
         <mrw:Password>{$this->password}</mrw:Password>
      </mrw:AuthInfo>
   </soap:Header>
   <soap:Body>
      <mrw:TransmEnvio>
         <mrw:request>
            <mrw:DatosEntrega>
               <mrw:Direccion>
                  <mrw:CodigoDireccion/>
                  <mrw:CodigoTipoVia/>
                  <mrw:Via>{$recipient->getAddress()}</mrw:Via>
                  <mrw:Numero/>
                  <mrw:Resto/>
                  <mrw:CodigoPostal>{$recipient->getPostcode()}</mrw:CodigoPostal>
                  <mrw:Poblacion>{$recipient->getCity()}</mrw:Poblacion>
                  <mrw:Provincia/>
                  <mrw:Estado/>
                  <mrw:CodigoPais>{$recipient->getCountryISO()}</mrw:CodigoPais>
                  <mrw:TipoPuntoEntrega/>
                  <mrw:CodigoPuntoEntrega/>
                  <mrw:CodigoFranquiciaAsociadaPuntoEntrega/>
                  <mrw:TipoPuntoRecogida/>
                  <mrw:CodigoPuntoRecogida/>
                  <mrw:CodigoFranquiciaAsociadaPuntoRecogida/>
                  <mrw:Agencia/>
               </mrw:Direccion>
               <mrw:Nif/>
               <mrw:Nombre>{$recipient->getName()}</mrw:Nombre>
               <mrw:Telefono>{$recipient->getPhone()}</mrw:Telefono>
               <mrw:Contacto>{$recipient->getName()}</mrw:Contacto>
               <mrw:ALaAtencionDe>{$recipient->getName()}</mrw:ALaAtencionDe>
               <mrw:Horario>
                  <mrw:Rangos>
                     <mrw:HorarioRangoRequest>
                        <mrw:Desde/>
                        <mrw:Hasta/>
                     </mrw:HorarioRangoRequest>
                  </mrw:Rangos>
               </mrw:Horario>
               <mrw:Observaciones>{$order->getNotes()}</mrw:Observaciones>
            </mrw:DatosEntrega>
            <mrw:DatosServicio>
               <mrw:Fecha>{$today}</mrw:Fecha>
               <mrw:NumeroAlbaran/>
               <mrw:Referencia>{$order->getOrderId()}</mrw:Referencia>
               <mrw:EnFranquicia/>
               <mrw:CodigoServicio>{$this->mrwServiceCode}</mrw:CodigoServicio>
               <mrw:DescripcionServicio/>
               <mrw:Frecuencia/>
               <mrw:CodigoPromocion/>
               <mrw:NumeroSobre/>
               <mrw:Bultos>
                  <mrw:BultoRequest>
                     <mrw:Alto/>
                     <mrw:Largo/>
                     <mrw:Ancho/>
                     <mrw:Dimension/>
                     <mrw:Referencia/>
                     <mrw:Peso/>
                  </mrw:BultoRequest>
               </mrw:Bultos>
               <mrw:NumeroBultos>{$parcels}</mrw:NumeroBultos>
               <mrw:Peso>{$order->getWeight()}</mrw:Peso>
               <mrw:NumeroPuentes/>
               <mrw:EntregaSabado/>
               <mrw:Entrega830/>
               <mrw:EntregaPartirDe/>
               <mrw:Gestion/>
               <mrw:Retorno/>
               <mrw:CodigoServicioRetorno/>
               <mrw:ConfirmacionInmediata/>
               {$crm}
               {$amount}
               <mrw:TipoMercancia/>
               <mrw:ValorDeclarado/>
               <mrw:ServicioEspecial/>
               <mrw:CodigoMoneda/>
               <mrw:ValorEstadistico/>
               <mrw:ValorEstadisticoEuros/>
               <mrw:Notificaciones>
                  <mrw:NotificacionRequest>
                     <mrw:CanalNotificacion>1</mrw:CanalNotificacion>
                     <mrw:TipoNotificacion>4</mrw:TipoNotificacion>
                     <mrw:MailSMS>{$recipient->getEmail()}</mrw:MailSMS>
                  </mrw:NotificacionRequest>
               </mrw:Notificaciones>
               <mrw:SeguroOpcional>
                  <mrw:CodigoNaturaleza/>
                  <mrw:ValorAsegurado/>
               </mrw:SeguroOpcional>
               <mrw:TramoHorario/>
               <mrw:PortesDebidos/>
               <mrw:Mascara_Tipos/>
               <mrw:Mascara_Campos/>
               <mrw:Asistente/>
            </mrw:DatosServicio>
         </mrw:request>
      </mrw:TransmEnvio>
   </soap:Body>
</soap:Envelope>
XML;
    }

    private function executeNewShipmentRequest(string $body): bool|string
    {
        if ($this->debugCarrierRequest) {
            $this->filesystem->dumpFile($this->logPath . 'mrw_request.xml', $body);
        }

        $response = $this->client->request('POST', $_ENV['MRW_SAGEC'], [
            'headers' => [
                'Content-Type' => 'text/xml;charset=UTF-8',
                'action' => '"http://www.mrw.es/TransmEnvio"',
            ],
            'body' => $body,
        ]);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return $response->getContent();
    }


    private function extractElementFromXml(string $xmlContent, string $nodeName): string
    {
        $dom = new \DOMDocument();
        // Evitamos warnings si hay namespaces complejos
        $dom->loadXML($xmlContent, LIBXML_NOERROR | LIBXML_NOWARNING);

        $nodes = $dom->getElementsByTagName($nodeName);

        if ($nodes->length === 0) {
            throw new \RuntimeException("No se encontró el nodo {$nodeName} en la respuesta SOAP.");
        }

        return $dom->saveXML($nodes->item(0));
    }

    private function executeGetLabelRequest(string $body): bool|string
    {
        if ($this->debugCarrierRequest) {
            $this->filesystem->dumpFile($this->logPath . 'mrw__label_request.xml', $body);
        }

        $response = $this->client->request('POST', $_ENV['MRW_SAGEC'], [
            'headers' => [
                'Content-Type' => 'text/xml;charset=UTF-8',
                'action' => '"http://www.mrw.es/GetEtiquetaEnvio"',
            ],
            'body' => $body,
        ]);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return $response->getContent();
    }

    private function prepareRequestLabels(string $tracking): string
    {
        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mrw="http://www.mrw.es/">
    <soapenv:Header>
        <mrw:AuthInfo>
            <mrw:CodigoFranquicia>$this->franquicia</mrw:CodigoFranquicia>
            <mrw:CodigoAbonado>$this->abonado</mrw:CodigoAbonado>
            <mrw:CodigoDepartamento></mrw:CodigoDepartamento>
            <mrw:UserName>$this->user</mrw:UserName>
            <mrw:Password>$this->password</mrw:Password>
        </mrw:AuthInfo>
    </soapenv:Header>
    <soapenv:Body>
        <mrw:GetEtiquetaEnvio>
            <mrw:request>
                <mrw:NumeroEnvio>$tracking</mrw:NumeroEnvio>
                <mrw:SeparadorNumerosEnvio></mrw:SeparadorNumerosEnvio>
                <mrw:TipoEtiquetaEnvio>3</mrw:TipoEtiquetaEnvio>
                <mrw:ReportTopMargin>1100</mrw:ReportTopMargin>
                <mrw:ReportLeftMargin>650</mrw:ReportLeftMargin>
            </mrw:request>
        </mrw:GetEtiquetaEnvio>
    </soapenv:Body>
</soapenv:Envelope>
XML;

    }

    public function getHistoryByTrackingNumberAfter(string $tracking, int $updatedAfter = 0): array
    {
        $body = $this->prepareRequestsForTracking($tracking);

        $response = $this->executeTrackingRequest($body);

        $dom = new \DOMDocument();
        // Evitamos warnings si hay namespaces complejos
        $dom->loadXML($response, LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new \DOMXPath($dom);

        $history = [];

        $xpath->registerNamespace(
            'a',
            'http://schemas.datacontract.org/2004/07/Mrw.TrackingService.Contracts.FrqAbonado'
        );

        $list = $xpath->query('//a:SeguimientoAbonado/a:Seguimiento');

        foreach ($list as $item) {
            $state = $this->serializer->deserialize(
                str_replace(['a:', 'i:'], '',
                    $dom->saveXML($item)),
                SeguimientoDTO::class,
                'xml',
                [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['PersonaEntrega'],
                ]
            );

            if ($state->Publicado->getTimestamp() > $updatedAfter) {
                $history[] = $state;
            }
        }

        return array_reverse($history);
    }

    private function executeTrackingRequest(string $body): bool|string
    {
        if ($this->debugCarrierRequest) {
            $this->filesystem->dumpFile($this->logPath . 'mrw__tracking_request.xml', $body);
        }

        $response = $this->client->request('POST', $this->urlTrackingServices, [
            'headers' => [
                'Content-Type' => 'text/xml;charset=UTF-8',
                'SOAPAction' => 'http://tempuri.org/ITrackingService/GetEnvios',
            ],
            'body' => $body,

        ]);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($this->debugCarrierRequest) {
            $this->filesystem->dumpFile($this->logPath . 'mrw__tracking_response.xml', $response->getContent());
        }

        return $response->getContent();
    }

    private function prepareRequestsForTracking(string $tracking): string
    {
        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:GetEnvios>
         <tem:login>$this->user</tem:login>
         <tem:pass>$this->password</tem:pass>
         <tem:codigoIdioma>3082</tem:codigoIdioma>
         <tem:tipoFiltro>0</tem:tipoFiltro>
         <tem:valorFiltroDesde>$tracking</tem:valorFiltroDesde>
         <tem:valorFiltroHasta>$tracking</tem:valorFiltroHasta>
         <tem:tipoInformacion>1</tem:tipoInformacion>
         <tem:codigoAbonado>$this->abonado</tem:codigoAbonado>
         <tem:codigoFranquicia>$this->franquicia</tem:codigoFranquicia>
      </tem:GetEnvios>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    }
}
