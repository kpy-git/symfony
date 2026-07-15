<?php

namespace App\Warehouse\Domain\Carrier\MRW;

use App\Warehouse\Domain\Exception\ShipmentException;
use App\Warehouse\Domain\ExpeditionableInterface;
use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

readonly class MRW implements ExpeditionableInterface
{
    private string $logPath;

    public function __construct(
        #[Autowire('%env(MRW_CORDOBA_FRANQUICIA)%')]
        private string $franquicia,
        #[Autowire('%env(MRW_CORDOBA_ABONADO)%')]
        private string $abonado,
        #[Autowire('%env(MRW_CORDOBA_USER)%')]
        private string $user,
        #[Autowire('%env(MRW_CORDOBA_PASSWORD)%')]
        private string $password,
        private Filesystem $filesystem,
        #[Autowire('%kernel.logs_dir%')]
        string $logPath,
    )
    {
        $this->logPath = $logPath . '/mrw/';
    }

    public function associatedService(): string
    {
        return 'CORDOBA';
    }

    /**
     * @throws ShipmentException
     */
    public function createShipment(Order $order, int $parcels): Shipment
    {
        $recipient = new MRWRecipient();
        $recipient->fillWith($order->getCustomer());

        $request = $this->prepareRequest($order, $recipient, $parcels);
        $this->filesystem->dumpFile($this->logPath . 'request.xml', $request);

        $response = $this->newShipmentRequest($request);

        $path = $this->parseResponse($response);

        return new Shipment(
            $order->getOrderId(),
            $this->createLabels($path, $order, $recipient),
            $this->getTrackingNumber($path)
        );

    }

    private function prepareRequest(Order $order, MRWRecipient $recipient, int $parcels): string
    {
        $xml = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:mrw="http://www.mrw.es/">
   <soap:Header>
      <mrw:AuthInfo>
         <mrw:CodigoFranquicia>' . $this->franquicia. '</mrw:CodigoFranquicia>
         <mrw:CodigoAbonado>' . $this->abonado. '</mrw:CodigoAbonado>
         <mrw:CodigoDepartamento/>
         <mrw:UserName>' . $this->user . '</mrw:UserName>
         <mrw:Password>' . $this->password . '</mrw:Password>
      </mrw:AuthInfo>
   </soap:Header>
   <soap:Body>
      <mrw:TransmEnvioEC>
         <mrw:request>
            <mrw:DatosEntrega>
               <mrw:Direccion>
                  <mrw:CodigoDireccion/>
                  <mrw:CodigoTipoVia/>
                  <mrw:Via>' . $recipient->getAddress() . '</mrw:Via>
                  <mrw:Numero/>
                  <mrw:Resto/>
                  <mrw:CodigoPostal>' . $recipient->getPostcode() . '</mrw:CodigoPostal>
                  <mrw:Poblacion>' . $recipient->getCity() . '</mrw:Poblacion>
                  <mrw:Provincia/>
                  <mrw:Estado/>
                  <mrw:CodigoPais>' . $recipient->getCountryISO() . '</mrw:CodigoPais>
                  <mrw:TipoPuntoEntrega/>
                  <mrw:CodigoPuntoEntrega/>
                  <mrw:CodigoFranquiciaAsociadaPuntoEntrega/>
                  <mrw:TipoPuntoRecogida/>
                  <mrw:CodigoPuntoRecogida/>
                  <mrw:CodigoFranquiciaAsociadaPuntoRecogida/>
                  <mrw:Agencia/>
               </mrw:Direccion>
               <mrw:Nif/>
               <mrw:Nombre>' . $recipient->getName() . '</mrw:Nombre>
               <mrw:Telefono>' . $recipient->getPhone() . '</mrw:Telefono>
               <mrw:Contacto>' . $recipient->getName()  . '</mrw:Contacto>
               <mrw:ALaAtencionDe>' . $recipient->getName()  . '</mrw:ALaAtencionDe>
               <mrw:Horario>
                  <mrw:Rangos>
                     <mrw:HorarioRangoRequest>
                        <mrw:Desde/>
                        <mrw:Hasta/>
                     </mrw:HorarioRangoRequest>
                  </mrw:Rangos>
               </mrw:Horario>
               <mrw:Observaciones>' . $order->getNotes() . '</mrw:Observaciones>
            </mrw:DatosEntrega>
            <mrw:DatosServicio>
               <mrw:Fecha>' . date('d/m/Y') . '</mrw:Fecha>
               <mrw:NumeroAlbaran/>
               <mrw:Referencia>' . $order->getOrderId() . '</mrw:Referencia>
               <mrw:EnFranquicia/>
               <mrw:CodigoServicio>0800</mrw:CodigoServicio>
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
               <mrw:NumeroBultos>' . $parcels . '</mrw:NumeroBultos>
               <mrw:Peso>' . $order->getWeight() . '</mrw:Peso>
               <mrw:NumeroPuentes/>
               <mrw:EntregaSabado/>
               <mrw:Entrega830/>
               <mrw:EntregaPartirDe/>
               <mrw:Gestion/>
               <mrw:Retorno/>
               <mrw:CodigoServicioRetorno/>
               <mrw:ConfirmacionInmediata/>';

        if ($order->isCRM()) {
            $xml .= '<mrw:Reembolso>O</mrw:Reembolso>
                    <mrw:ImporteReembolso>' . str_replace(".", ",", $order->getCrm()) . '</mrw:ImporteReembolso>';
        }

        $xml .= '<mrw:TipoMercancia/>
               <mrw:ValorDeclarado/>
               <mrw:ServicioEspecial/>
               <mrw:CodigoMoneda/>
               <mrw:ValorEstadistico/>
               <mrw:ValorEstadisticoEuros/>
               <mrw:Notificaciones>
                  <mrw:NotificacionRequest>
                     <mrw:CanalNotificacion>1</mrw:CanalNotificacion>
                     <mrw:TipoNotificacion>4</mrw:TipoNotificacion>
                     <mrw:MailSMS>' . $recipient->getEmail() . '</mrw:MailSMS>
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
      </mrw:TransmEnvioEC>
   </soap:Body>
</soap:Envelope>';

        return $xml;
    }

    private function newShipmentRequest(string $request): bool|string
    {
        $header = [
            'Accept-Encoding: gzip,deflate',
            'Content-Type: application/soap+xml;charset=UTF-8;action="http://www.mrw.es/TransmEnvioEC"',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_ENV['MRW_SAGEC']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($ch);

    }

    /**
     * @throws ShipmentException
     */
    private function parseResponse(bool|string $response): \SimpleXMLElement
    {
        if (!$response) {
            throw new ShipmentException('Ha ocurrido un error al comunicarse con el transportista');
        }

        $xml = simplexml_load_string($response);

        if ($xml === false) {
            $this->filesystem->dumpFile($this->logPath . 'response.xml', $response);
            throw new ShipmentException('Ha ocurrido un error al recibir la respuesta del transportista');
        }

        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $path = $xml->xpath('//soap:Body')[0];

        if ((int)$path->TransmEnvioECResponse->TransmEnvioECResult->Estado === 0) {
            throw new ShipmentException("Ha ocurrido un error al generar el envío:\n" .
                $path->TransmEnvioECResponse->TransmEnvioECResult->Mensaje);
        }

        return $path;
    }

    private function createLabels(\SimpleXMLElement $path, Order $order, MRWRecipient $recipient): string
    {
        $zpl = '';

        foreach ($path->TransmEnvioECResponse->TransmEnvioECResult->Labels->Label as $label) {
            $zpl .= $this->createLabel($order->getOrderId(),
                $label->NumEnvioLecturaPlataformas,
                $label->CodigoFranquiciaDestino,
                $label->NombreFranquiciaDestino,
                $label->CodigoSaca,
                $label->NombreSaca,
                $label->CodigoRuta,
                $label->BultoExpedicion,
                ceil($order->getWeight()),
                $order->getNotes(),
                $label->NombreServicio,
                $recipient->getPhone(),
                $recipient->getState(),
                $recipient->getName(),
                $recipient->getName(),
                $recipient->getAddress(),
                $recipient->getPostcode(),
                $recipient->getCity(),
                $order->getCrm(),
                $label->DescripcionRevisionEnFranquicia === 'REVISAR EN FRANQUICIA'
            );
        }

        return $zpl;
    }

    private function createLabel($pedido, $codigoBarras, $codFranquiciaDestino, $nombreFranquiciaDestino, $saca,
                                  $nombreSaca, $ruta, $bultoExpedicion, $kilos, $observaciones, $servicio, $telefono, $provincia, $atencion,
                                  $nombre, $direccion, $codigoPostal, $localidad, $reembolso, $RF): string
    {

        if ($RF) {
            // si el código postal no se encuentra en MRW, no se generará ningún código de barras y se marca la etiqueta con RF
            $codigoBarrasConFormato = $codigoBarras = "";
        } else {
            $codigoBarrasConFormato = substr($codigoBarras, 0, 1) . " " . substr($codigoBarras, 1, 5) . " " . substr($codigoBarras, 6, 5) . "0" . substr($codigoBarras, 12, 7) . " " . substr($codigoBarras, 19);

            $codigoBarras = "^FD" . $codigoBarras . "^FS";
        }

        // SD15 -> SD23: darkness level
        $zpl = "CT~~CD,~CC^~CT~
        ^XA~TA000~JSN^LT0^MNW^MTD^PON^PMN^LH0,0^JMA^PRE,9~SD23^JUS^LRN^CI28^XZ
        ~DG000.GRF,01792,008,
        ,:::::::::::::::K03,K0380,K03C0,K03E0,K03F8,::K03F880,K03F8C0,K03F8E0,K03F8F0,K03F8FA,K03F8FE,K03F8FF80,K03F8FFC0,K03F8FFE0,K03F8FHF0,K03F8FHF8,K01F8FHFE,K02F8FIF80,L0F8FIFC0,L038FIFE0,L038FJF0,L028FJF8,N0KFC,N0KFE,N0KFC,N07FIFE,N01FIFC,O0JFC,O07FHFC,K03A003FHFE,K03F001FHFC,K03F8A0FHFE,K03F8FC3FFC,K03F8FJFE,K03F8FJFC,::K03F8FJFE,K03F8FJFC,K03F8FJFE,K03F8FJFC,K03F8FJFE,K03F8FJFC,L0F8FJFC,L078FJFC,L038FFABFE,L018FFC07C,M08FFE0,N0IF0,N0IFA,N07FFE,N03FHF80,K03001FHFC0,K03E003FFE0,K03F803FHF0,K03F8A0FHF8,K03F8F07FF8,K03F8FFBFFA,K03F8FJFC,::K03F8FJFE,K03F8FJFC,K03F8FJFE,K03F8FJFC,K03F8FJFE,K03F8FJFC,::K03F8FJFE,K01F8FJFC,L028FJFE,N0KFC,N03FIFE,O07FHFC,P0BFFC,P01FFC,L03FE003FE,L07FF0H01C,L0IFE0H08,K01FHFE,K03FIF,K03FIF80,:K03FIFC0,K03FIFE002,K03FIFE01C,K03FIFE03E,K03FIFE07C,K03FIFE3FE,K03FJF7FC,K03FLFC,:K03FF2FIFE,K03FF07FHFC,K03FE03FHFE,K03FE03FHFC,K03FE03FHFE,K03FE03FHFC,::K03FE03FHFE,K03FE03FHFC,K03FE03FHFE,K03FE03FHFC,K03FE03FHFE,K03FE03FHF8,K03FF83FFE0,K03FHF3FFC0,K03FJFE,K03FKF,K03FKFE0,K03FLF0,K03FLF8,::K03FLFC,K03FLFE,K03FLFC,K03FLFE,K01FLFC,L0MFE,L01FKFC,M0BFJFC,N07FIFC,N02FIFE,O01FHFC,K0380H02FFE,K03F0I03FC,K03FF80H03E,K03FHFJ04,K03FHFE,K03FIFC0,K03FJFA,K03FKF80,K03FKFE8,K03FLFC,K03FLFE,K03FLFC,::K03FLFE,K03FLFC,K03FLFE,K03FLFC,K03FLFE,K03FLFC,::K03FLFE,K03FHFC7FFC,K03FHFE0FFE,K01FIF807C,L0JFE02A,L07FIF0,L03FIF8,M07FHFE,M03FIF80,M01FIFC0,N0JFE0,N01FIF8,O0JFE,O03FHFC,P0IFC,P07FFC,L0I2ABFFE,K01FLFC,K03FLFE,K03FLFC,K03FLFE,K03FLFC,::K03FLFE,K03FLFC,K03FLFE,K03FLF4,K03FIFA2,K03FHFC,K03FIFA0,K03FJF0,K03FKF80,K03FKFC0,K03FKFE0,K03FLF0,K03FLF8,K03FLFC,::K03FLFE,K03FLFC,K03FLFE,K017FKFC,L02FKFE,M01FJFC,N03FIFC,O07FHFC,P0BFFE,Q0HFC,Q02FE,R01C,S0A,,:::~DG001.GRF,19200,040,
        ,::::::::::::::K0iNFE,J010iM01,:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::J01FiNF,,::::::::::::::::::::::::~DG002.GRF,29440,040,
        ,:::::::L0iMFE,K010iL01,:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::K01FiMF,~DG003.GRF,121600,100,
        ,:::::::::::::H01FoRFC0,H010oR020,::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::H01FoRFC0,,::::::::::::::::::::::::::::::
        ^XA
        ^MMT
        ^PW799
        ^LL1199
        ^LS0
        ^BY5,3,154^FT748,1118^BCB,,N,N,,A
        {$codigoBarras}
        ^FT0,1184^XG000.GRF,1,1^FS
        ^FT96,480^XG001.GRF,1,1^FS
        ^FT96,1184^XG002.GRF,1,1^FS
        ^FT0,1216^XG003.GRF,1,1^FS
        ^FT772,847^A0B,34,33^FH\^FD{$codigoBarrasConFormato}^FS
        ^FT561,987^A0B,34,33^FH\^FD" . date("d/m/Y") . "^FS
        ^FT561,1174^A0B,34,33^FH\^FD{$kilos}^FS
        ^FT516,1092^A0B,34,33^FH\^FD{$bultoExpedicion}^FS
        ^FT530,612^A0B,34,33^FB550,4,0,L,0^FH\^FD{$observaciones}^FS
        ^FT50,972^A0B,37,36^FH\^FD{$servicio}^FS
        ^FT381,1126^A0B,28,28^FH\^FD{$telefono}^FS
        ^FT381,1179^A0B,28,28^FH\^FDTel:^FS
        ^FT306,1045^A0B,28,28^FH\^FD{$provincia}^FS
        ^FT344,1090^A0B,28,28^FH\^FD{$atencion}^FS
        ^FT266,1180^A0B,34,34^FH\^FD{$localidad}^FS
        ^FT220,1180^A0B,36,34^FB700,2,0,L,0^FH\^FD{$direccion}^FS
        ^FT474,1178^A0B,40,40^FH\^FD{$nombreSaca}^FS
        ^FT432,1177^A0B,34,34^FH\^FD{$nombreFranquiciaDestino}^FS
        ^FT204,137^A0B,39,31^FH\^FDPagados^FS
        ^FT204,327^A0B,39,38^FH\^FDN^FS
        ^FT58,455^A0B,28,28^FH\^FD{$pedido}^FS";

        if ($reembolso > 0) {
            $zpl .= "^FT375,252^A0B,39,57^FH\^FD" . str_replace(".", ",", $reembolso) . "^FS";
        }

        $zpl .= "^FT149,164^A0B,45,45^FH\^FD{$codFranquiciaDestino}^FS
        ^FT154,328^A0B,39,38^FH\^FD^FS
        ^FT201,244^A0B,34,33^FH\^FDPortes:^FS
        ^FT202,450^A0B,34,33^FH\^FDRetorno:^FS
        ^FT153,449^A0B,34,36^FH\^FDGestión^FS
        ^FT517,1176^A0B,34,33^FH\^FDBulto:^FS
        ^FT29,455^A0B,17,16^FH\^FDR.ENVÍO:^FS
        ^FT97,1179^A0B,28,28^FH\^FD" . $_ENV['COMPANY'] . "^FS
        ^FT150,1180^A0B,40,40^FH\^FD{$nombre}^FS";

        if ($reembolso > 0) {
            $zpl .= "^FT374,446^A0B,34,38^FH\^FDTotal:^FS";
        }

        $zpl .= "^FT306,1127^A0B,34,34^FH\^FD{$codigoPostal}^FS
        ^FT561,1070^A0B,34,33^FH\^FDKG^FS
        ^FT347,1178^A0B,28,28^FH\^FDA/A de:^FS
        ^FT306,1179^A0B,34,34^FH\^FDCP:^FS";

        if ($RF) {
            // si el código postal no existe, se marca para revisar en franquicia RF
            $zpl .= "^FT678,310^A0B,113,74^FH\^FDRF^FS";
        }

        // ^FT735,321^A0B,39,31^FH\^FD@MRWCPC@^FS

        $zpl .= "^FT443,676^A0B,46,45^FH\^FD{$ruta}^FS";

        if ($reembolso > 0) {
            $zpl .= "^FT337,445^A0B,28,28^FH\^FDReembolso^FS";
        }

        $zpl .= "^FT476,640^A0B,23,24^FH\^FD{$saca}^FS
        ^FO215,14^GB0,444,2^FS
        ^FO392,16^GB196,598,1^FS
        ^FO483,615^GB0,567,1^FS
        ^LRY^FO451,616^GB0,40,30^FS^LRN
        ^LRY^FO112,18^GB0,226,45^FS^LRN
        ^LRY^FO13,461^GB0,512,50^FS^LRN";

        if ($reembolso > 0) {
            $zpl .= "^LRY^FO341,16^GB0,334,45^FS^LRN";
        }

        $zpl .= "^PQ1,0,1,Y^XZ
        ^XA^ID000.GRF^FS^XZ
        ^XA^ID001.GRF^FS^XZ
        ^XA^ID002.GRF^FS^XZ
        ^XA^ID003.GRF^FS^XZ";

        return str_replace("'", "", $zpl); // para evitar problemas con ' al guardar en la bbdd
    }

    private function getTrackingNumber(\SimpleXMLElement $path): string
    {
        return $path->TransmEnvioECResponse->TransmEnvioECResult->Estado->NumeroEnvio;
    }
}
