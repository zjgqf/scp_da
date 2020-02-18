<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\ArrayToXml\ArrayToXml;

class ShipMessageController extends Controller
{
    public function index()
    {
        return view('scp.index');
    }

    public function show(Request $request)
    {
        $type = $request->type;
        $mbl_no = $request->mbl_no;

        $data = $this->getScpData($mbl_no);

        if(count($data) > 0) {
            $result = $this->insertArray($data);
            return view('scp.index')->with('xml_data', $result['xml'])->with('file_path', $result['path']);
        }

        return redirect()->route('shipMessage.index')->with('danger','数据查询失败！');
    }

    public function download(Request $request)
    {
        //return $request->file_path;
        $path = storage_path('app/xml/' . $request->file_path);
        return response()->download($path);
    }

    public function getScpData($mbl_no)
    {
        $data = $this->prd_oracle->select("SELECT MFOR.VESSEL_NAME,
                                            MFOR.VOYAGE,
                                            (SELECT MD.IMO_NO
                                              FROM FMSUSER.MD_VESSEL MD
                                              WHERE MD.VESSEL_CODE = MFOR.VESSEL_CODE) AS IMO_NO,
                                            MFOR.CUSTOMS_CLEARANCE_ZONE_CODE,
                                            BKCO.CONTAINER_NO,
                                            BKCO.CONTAINER_SIZE_TYPE,
                                            MFOC.FCL_LCL_EMPTY,
                                            MFOR.FIRST_PORT_AREA_NAME,
                                            MFOR.FIRST_PORT_AREA_CODE 
                                            FROM FMSUSER.MF_ORDER MFOR
                                            LEFT JOIN FMSUSER.MF_ORDER_2_CTN MFOC
                                              ON MFOR.MF_ORDER_ID = MFOC.MF_ORDER_ID
                                            LEFT JOIN FMSUSER.BK_CONTAINER BKCO
                                              ON MFOC.BK_CONTAINER_ID = BKCO.BK_CONTAINER_ID
                                            WHERE MFOR.MBL_NO = :mbl_no
                                            AND MFOR.SETTLE_OFFICE='WYCJ_ZJG'
                                            AND MFOR.IS_SHUT_OFF_LOAD <> 'Y'
                                            AND MFOR.IS_DELETED <> 'Y'
                                            AND MFOR.IS_VIRTUAL_ORDER IS NULL
                                            ", ['mbl_no' => $mbl_no]);
        return $data;
    }

    public function insertArray($data)
    {
        $timeString = Carbon::now()->format('yymdHisu');
        $transportEquipment = [];
        foreach ($data as $k => $v) {
            if($k==0) {
                $orderData = $v;
            }
            $transportEquipment[$k] = [
                'EquipmentIdentification' => [
                    'ID' => $v->container_no,
                ],
                'CharacteristicCode' => $this->container($v->container_size_type),
                'FullnessCode' => $v->fcl_lcl_empty == 'E' ? 4:5,
            ];
        }

        $head = [
            'MessageID' => '230560825683X-' . substr($timeString, 0, 17),
            'FunctionCode' => 2,
            'MessageType' => 'WLJK_MT3101',
            'SenderID' => '230560825683X',
            'ReceiverID' => $orderData->customs_clearance_zone_code,
            'SendTime' => substr($timeString, 0, 17),
            'Version' => '1.0'
        ];

        $declaration = [
            'DeclarationOfficeID' => $orderData->customs_clearance_zone_code,
            'BorderTransportMeans' => [
                'JourneyID' => $orderData->voyage,
                'TypeCode' => 1,
                'ID' => 'UN' . $orderData->imo_no,
                'Name' => $orderData->vessel_name,
            ],
            'UnloadingLocation' => [
                'ID' => ($orderData->first_port_area_code == 'YOJ1' ? 'CNZJG230015':'CNZJG230103') . '/' . $orderData->customs_clearance_zone_code,
                'ArrivalDate' => substr($timeString, 0, 8)
            ],
            'TransportEquipment' => $transportEquipment,
            'AdditionalInformation' => [
                'LineFlag' => 1
            ]
        ];

        $result = [
            'Head' => $head,
            'Declaration' => $declaration
        ];

        $xml = ArrayToXml::convert($result, [
           'rootElementName' => 'Manifest',
            '_attributes' => [
                'xmlns' => "urn:Declaration:datamodel:standard:CN:WLJK_MT3101:1",
                'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance"
            ]
        ], true, 'utf-8');

        $path = 'CN_WLJK_MT3101_1p0_' . '230560825683X_' . substr($timeString, 0, 17) . '.xml';

        Storage::disk('local')->put('xml/' . $path, $xml);

        return [
            'xml' => $xml,
            'path' => $path,
        ];
    }

    //箱型对照
    public function container($type)
    {
        $container = [
            '20GP' => '22G0',
            '40GP' => '42G0',
            '20HC' => '25G1',
            '40HC' => '45G1',
        ];
        return $container[$type];
    }

    function arrayToXml($array, $rootElement = null, $xml = null) {
        $_xml = $xml;
        // 如果没有$rootElement，则插入$rootElement
        if ($_xml === null) {

            $_xml = new \SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');

        }
        // 访问所有键值对
        foreach ($array as $k => $v) {
            // 如果有嵌套数组
            if (is_array($v)) {
                // 调用嵌套数组的函数
                $this->arrayToXml($v, $k, $_xml->addChild($k));
            }
            else {
                $_xml->addChild($k, $v);
            }
        }
        return $_xml->asXML();
    }


}
