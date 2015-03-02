<?php

namespace Nbobtc\Bitcoind;

/**
 * @author Joshua Estes
 */
class Client implements ClientInterface
{

    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var string
     */
    protected $cacert;

    /**
     * @var string
     */
    protected $magic_byte;

    /**
     * @var string
     */
    protected $magic_p2sh_byte;


    /**
     *
     * @param string|null $dsn
     * @param string|null $cert The file path to a certificate you'd like to use for SSL verification
     * @param string|null $magic_byte magic_byte for addresses
     * @param string|null $magic_p2sh_byte magic_p2sh_byte
     *
     */
    public function __construct($dsn = null, $cacert = null, $magic_byte = null, $magic_p2sh_byte = null)
    {
      $this->dsn = $dsn;
      $this->cacert = $cacert;
      $this->magic_byte = $magic_byte;
      $this->magic_p2sh_byte = $magic_p2sh_byte;

    }

    /**
     * @param string $dsn
     * @return Client
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
        return $this;
    }

    /**
     * @param string $cacert
     * @return Client
     */
    public function setCacert($cacert)
    {
        $this->cacert = $cacert;
        return $this;
    }

    /**
     * @param string $version
     * @return Client
     */
    public function setMagicByte($magic_byte)
    {
        $this->magic_byte = $magic_byte;
        return $this;
    }

    /**
     * @param string $version
     * @return Client
     */
    public function getMagicByte()
    {
        return $this->magic_byte;
    }



    /**
     * @param string $version
     * @return Client
     */

    public function setMagicP2shByte($magic_p2sh_byte) {
      $this->magic_p2sh_byte = $magic_p2sh_byte;
      return $this;
    }


    /**
     * @param string $version
     * @return Client
     */

    public function getMagicP2shByte() {
      return $this->magic_p2sh_byte;
    }





    /**
     * @param string       $method
     * @param string|array $params
     * @param string       $id
     * @throw Exception
     * @return StdClass
     */
    public function execute($method, $params = null, $id = null)
    {
        $ch = curl_init($this->dsn);

        if (null === $params || "" === $params) {
            $params = array();
        } elseif (!is_array($params)) {
            $params = array($params);
        }

        $json = json_encode(array('method' => $method, 'params' => $params, 'id' => $id));
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 60,
        ));

        if($this->cacert) curl_setopt($ch, CURLOPT_CAINFO, $this->cacert);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        if (false === $response) {
            throw new \Exception('The server is not available.');
        }

        if ($status['http_code'] != 200) {
            if ($response && ($json = json_decode($response, true))) {
                throw new \Exception($json['error']['message'], $json['error']['code']);
            }
            throw new \Exception('The server status code is '.$status['http_code'].'.');
        }

        $stdClass = json_decode($response);

        if (!empty($stdClass->error)) {
            throw new \Exception($stdClass->error->message, $stdClass->error->code);
        }

        return $stdClass;
    }
}
