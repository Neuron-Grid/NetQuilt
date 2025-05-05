<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Table {{%allocation_cidr}}
 *
 * @property int    $id
 * @property int    $block_id
 * @property string $cidr        CIDR
 * @property int    $ip_version  4|6
 *
 * @property-read AllocationBlock|null $block
 */
final class AllocationCidr extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%allocation_cidr}}';
    }

    /** @inheritdoc */
    public function rules(): array
    {
        return [
            [['block_id', 'cidr', 'ip_version'], 'required'],
            [['block_id'], 'integer'],
            [['cidr'], 'string'],
            ['ip_version', 'in', 'range' => [4, 6]],
            [['block_id', 'cidr', 'ip_version'], 'unique',
                'targetAttribute' => ['block_id', 'cidr', 'ip_version'],
            ],
            [['cidr', 'ip_version'], 'unique',
                'targetAttribute' => ['cidr', 'ip_version'],
            ],
            [['block_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => AllocationBlock::class,
                'targetAttribute' => ['block_id' => 'id'],
            ],
        ];
    }

    /** @codeCoverageIgnore */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'block_id'   => 'Block ID',
            'cidr'       => 'CIDR',
            'ip_version' => 'IP Version',
        ];
    }

    public function getBlock(): ActiveQuery
    {
        return $this->hasOne(AllocationBlock::class, ['id' => 'block_id']);
    }
}