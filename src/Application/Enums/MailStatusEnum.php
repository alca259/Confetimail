<?php
abstract class MailStatus extends BasicEnum
{
	const NoEnviado = 0;
	const NoEnviadoText = "No enviado";
	const ErroresAlEnviar = 1;
	const ErroresAlEnviarText = "Han ocurrido problemas al enviarlo";
	const EnviadoConExito = 2;
	const EnviadoConExitoText = "Se ha enviado con exito";

	public static function GetTextFor($id)
    {
		$returnData = "";
		switch ($id)
        {
			case self::NoEnviado:
				$returnData = self::NoEnviadoText;
				break;
			case self::ErroresAlEnviar:
				$returnData = self::ErroresAlEnviarText;
				break;
			case self::EnviadoConExito:
				$returnData = self::EnviadoConExitoText;
				break;
		}
		return $returnData;
	}
}
?>