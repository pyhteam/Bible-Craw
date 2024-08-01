<?php
class Client
{

    private function setCurlOptions($ch, $url, $customRequest = null, $data = null)
    {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($customRequest) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // set proxy for curl
        // Proxy Configuration
        $proxy = [
            'host' => '172.20.10.1',
            'port' => '1082'
        ];
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
        }
    }

    public function Get($url, $params = array())
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init();
        $this->setCurlOptions($ch, $url);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function Post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        $this->setCurlOptions($ch, $url, null, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function Delete($url, $params = array())
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init();
        $this->setCurlOptions($ch, $url, "DELETE");
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function Put($url, $data)
    {
        $ch = curl_init();
        $this->setCurlOptions($ch, $url, "PUT", $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
