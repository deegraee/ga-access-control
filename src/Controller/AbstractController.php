<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class AbstractController extends  \Symfony\Bundle\FrameworkBundle\Controller\AbstractController{
	protected function parseRequest() {
		$content_type = trim(explode(';', $_SERVER["CONTENT_TYPE"])[0]);
		$raw = file_get_contents('php://input');
		switch ($content_type) {
			case '':
			case 'text/plain':
			case 'application/json':
				return json_decode($raw, TRUE);
				break;
			case 'application/x-www-form-urlencoded':
				$parameters = explode('&', $raw);
				$ret = [];
				foreach ($parameters as $parameter) {
					$temp = explode('=', $parameter);
					if (strlen($temp[0]) > 0) {
						if (strlen($temp[1]) > 0) {
							$ret[$temp[0]] = urldecode($temp[1]);
						}
						else {
							$ret[$temp[0]] = '';
						}
					}
				}
				return $ret;
				break;
			default:
				trigger_error('Content-type "'.$content_type.'" is currently not supported', E_USER_WARNING);
				return;
				break;
		}
	}
}