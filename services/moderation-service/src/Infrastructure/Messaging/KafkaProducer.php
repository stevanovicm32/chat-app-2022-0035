<?php

namespace App\Infrastructure\Messaging;

class KafkaProducer
{
    private ?\RdKafka\Producer $producer = null;

    public function publish(string $topic, array $payload, ?string $key = null): void
    {
        $producer = $this->getProducer();
        $topicHandle = $producer->newTopic($topic);
        $topicHandle->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload), $key ?? uniqid());
        $producer->poll(0);
        $result = $producer->flush(10000);
        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new \RuntimeException('Kafka publish failed: ' . rd_kafka_err2str($result));
        }
    }

    private function getProducer(): \RdKafka\Producer
    {
        if ($this->producer) {
            return $this->producer;
        }

        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKERS', 'kafka:9092'));
        $this->producer = new \RdKafka\Producer($conf);

        return $this->producer;
    }
}
