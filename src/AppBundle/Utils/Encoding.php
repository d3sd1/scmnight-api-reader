<?php

namespace AppBundle\Utils;


class Encoding
{

    function decodeDniCertificate($cert)
    {
        return openssl_x509_parse(base64_decode($cert));
    }
    function base64EncodeUrl($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function base64DecodeUrl($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    
    function parseDniCertificate($data)
    {
        $dniInfo = array();
        $z = explode(',', preg_replace("/ \([^)]+\)/", "", $data['subject']['CN']));
        $surnames = explode(" ", $z[0]);
        $dniInfo["surname1"] = $surnames[0];
        $dniInfo["surname2"] = $surnames[1];
        $dniInfo["name"] = substr($z[1], 1);
        $dniInfo["serialNumber"] = $data["serialNumber"];
        $dniInfo["nationality"] = $data['subject']['C'];
        $dniInfo["dni"] = $data['subject']['serialNumber'];
        $dniInfo["biometric"] = $this->base64EncodeUrl($data['extensions']['biometricInfo']);
        $birthdate = substr($data['extensions']['subjectDirectoryAttributes'], -15, 8);
        $yyyy = substr($birthdate, 0, 4);
        $mm = substr($birthdate, 4, 2);
        $dd = substr($birthdate, 6, 2);
        $dniInfo["birthdate"] = (new \DateTime($yyyy . "-" . $mm . "-" . $dd));
        return $dniInfo;
    }
}
