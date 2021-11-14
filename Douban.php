<?php
use GuzzleHttp\Exception\GuzzleException;

require_once "workflows.php";
require_once "vendor/autoload.php";

class Douban
{
    protected $workflows;

    protected $client;

    protected $url = "https://api.wmdb.tv/api/v1/movie/search/?q=";

    /**
     * douban constructor.
     *
     * @param $workflows
     */
    public function __construct()
    {
        $this->workflows = new Workflows();
        $this->client = new GuzzleHttp\Client();
    }

    public function query($keyword)
    {
        $data = $this->get($this->url, $keyword);
        $name = $description = $poster = "";

        foreach ($data as $datum) {
            foreach ($datum["data"] as $value) {
                $name = $value["name"];
                $description = $value["description"];
                $poster = $value["poster"];
            }
            $doubanRating = $datum["doubanRating"] ?: "暂无评分";
            $name .= "【". $doubanRating . "】";
            $this->workflows->result(
                $datum["doubanId"],
                $datum["doubanId"],
                $name,
                $description,
                "images/movie.png"
            );
        }

        echo $this->workflows->toxml();
    }

    /**
     * 发送GET 请求
     *
     * @param $uri
     * @param $query
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($uri, $query = "")
    {
        return $this->request("get", $uri.$query);
    }

    /**
     * 发送请求
     *
     * @param $method
     * @param $uri
     *
     * @return mixed
     * @throws \Exception
     */
    public function request($method, $uri)
    {
        try {
            /** @var \GuzzleHttp\Client $client */
            $result = $this->client->request($method, $uri, [
                'headers' => [
                    'Content-Type' => 'application/json;charset=UTF-8',
                ],
            ]);

            $data = $result->getBody()->getContents();

            return json_decode($data, true) ? : $data;
        } catch (GuzzleException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}