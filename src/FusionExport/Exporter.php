<?php

namespace FusionExport;

use FusionExport\Exceptions\ConnectionRefusedException;
use FusionExport\Exceptions\ServerException;

class Exporter
{
    private $exportDoneListener;

    private $exportStateChangedListener;

    private $exportConfig;

    private $exportServerHost;

    private $exportServerPort;

    private $exportServerSecure;

    private $client;

    public function __construct(ExportConfig $exportConfig)
    {
        $this->exportConfig = $exportConfig;
    }

    public function setExportConnectionConfig($exportServerHost, $exportServerPort, $isSecure)
    {
        $this->exportServerHost = $exportServerHost;
        $this->exportServerPort = $exportServerPort;
        $this->exportServerSecure = boolval($isSecure);
    }

		public function sendToServer() {
			$this->client = new \GuzzleHttp\Client(['verify' => FALSE]);

			$configData = $this->exportConfig->getFormattedConfigs();
            $url = $this->exportServerHost . ':' . $this->exportServerPort;
            $apiUrl = $this->getApiUrl($url);
			$multipartArray = $this->createMultipartData($configData);

			try {
				$response = $this->client->request('POST', $apiUrl, [
					'multipart' => $multipartArray
				]);
			} catch (\GuzzleHttp\Exception\ConnectException $err) {
				throw new ConnectionRefusedException($this->exportServerHost, $this->exportServerPort);
			} catch (\GuzzleHttp\Exception\ServerException $err) {
				$response = $err->getResponse();
				$statusCode = $response->getStatusCode();

				if ($statusCode === 500) {
					$errMsg = $response->getBody()->getContents();

					try {
						$errMsg = json_decode($errMsg)->error;
					} catch (\Exception $err) {
						// continue regardless of error
					}

					throw new ServerException($errMsg);
				}

				throw $err;
			}

			if (isset($configData['payload'])) {
				unlink($configData['payload']);
			}

			return $response->getBody()->getContents();
		}

    private function createMultipartData($configData)
    {
        $multipart = array();

        if (count($configData) > 0) {
            foreach ($configData as $key => $value) {
                $arr = array();
                $arr['name'] = $key;
                if (strcasecmp($key, 'payload') == 0) {
                    $arr['contents'] = fopen($value, 'r');
                } else {
                    $arr['contents'] = $value;
                }
                array_push($multipart, $arr);
            }
        }

        return $multipart;
    }

    private function getApiUrl($url) {
        $api = "/api/v2.0/export";
        if(boolval($this->exportServerSecure) === TRUE) {
            $authUrl = "https://" . $url;
            try {
                $this->client->request('GET', $authUrl);
                return $authUrl . $api;
            } catch (\GuzzleHttp\Exception\RequestException $err) {
                echo "Warning: HTTPS server not found, overriding requests to an HTTP server";
            }
        }
        return "http://". $url . $api;
    }

    private function checkExportError($exportResult)
    {
        $exportResult = json_decode($exportResult);

        if (array_key_exists('error', $exportResult)) {
            return $exportResult->error;
        }
    }
}
