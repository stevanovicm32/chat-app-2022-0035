<?php

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Messaging\Consumers\MessageCreatedConsumer;
use App\Infrastructure\Messaging\Consumers\ProcessingErrorConsumer;
use Illuminate\Console\Command;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume Kafka topics for Moderation service';

    public function handle(
        MessageCreatedConsumer $messageConsumer,
        ProcessingErrorConsumer $errorConsumer
    ): int {
        $brokers = env('KAFKA_BROKERS', 'kafka:9092');
        $topics = [
            MessageCreatedConsumer::TOPIC_IN,
            ProcessingErrorConsumer::TOPIC_IN,
        ];
        $group = env('KAFKA_GROUP_ID', 'moderation-service-group');

        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $brokers);
        $conf->set('group.id', $group);
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.auto.commit', 'true');

        $kafkaConsumer = new \RdKafka\KafkaConsumer($conf);
        $kafkaConsumer->subscribe($topics);

        $this->info('Moderation Kafka consumer listening on: ' . implode(', ', $topics));

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
            if (!is_array($payload)) {
                continue;
            }

            match ($message->topic_name) {
                MessageCreatedConsumer::TOPIC_IN => $messageConsumer->handle($payload),
                ProcessingErrorConsumer::TOPIC_IN => $errorConsumer->handle($payload),
                default => null,
            };
        }
    }
}
