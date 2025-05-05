<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Table {{%merged_cidr}}
 *
 * @property int    $id
 * @property string $cidr        CIDR
 * @property string $region_id
 * @property int    $ip_version  4|6
 *
 * @property-read Region|null $region
 */
final class MergedCidr extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%merged_cidr}}';
    }

    /** @inheritdoc */
    public function rules(): array
    {
        return [
            [['region_id', 'cidr', 'ip_version'], 'required'],
            [['cidr'], 'string'],
            [['region_id'], 'string', 'max' => 2],
            ['ip_version', 'in', 'range' => [4, 6]],
            [['region_id', 'cidr', 'ip_version'], 'unique',
                'targetAttribute' => ['region_id', 'cidr', 'ip_version'],
            ],
            [['cidr', 'ip_version'], 'unique',
                'targetAttribute' => ['cidr', 'ip_version'],
            ],
            [['region_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => Region::class,
                'targetAttribute' => ['region_id' => 'id'],
            ],
        ];
    }

    /** @codeCoverageIgnore */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'cidr'       => 'CIDR',
            'region_id'  => 'Region ID',
            'ip_version' => 'IP Version',
        ];
    }

    public function getRegion(): ActiveQuery
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }
}