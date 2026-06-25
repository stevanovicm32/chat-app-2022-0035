<?php

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Messaging\Consumers\SuspendProcessingConsumer;
use Illuminate\Console\Command;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume Kafka topics for Identity service';

    public function handle(SuspendProcessingConsumer $consumer): int
    {
        $brokers = env('KAFKA_BROKERS', 'kafka:9092');
        $topic = SuspendProcessingConsumer::TOPIC_IN;
        $group = env('KAFKA_GROUP_ID', 'identity-service-group');

        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $brokers);
        $conf->set('group.id', $group);
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.auto.commit', 'true');

        $kafkaConsumer = new \RdKafka\KafkaConsumer($conf);
        $kafkaConsumer->subscribe([$topic]);

        $this->info("Identity Kafka consumer listening on: {$topic}");

        while (true) {
            $message = $kafkaConsumer->consume(120 * 1000);
            if ($message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                continue;
            }
            if ($message->err) {
                $this->error(rd_kafka_err2str($message->err));
                continue;
            }

            $payload = json_decode($message->payload, true);
            if (is_array($payload)) {
                $consumer->handle($payload);
                $this->line('Processed suspend event for korisnik: ' . ($payload['korisnikId'] ?? '?'));
            }
        }
    }
}
