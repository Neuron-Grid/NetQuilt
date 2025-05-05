<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Table {{%region_stat}}
 * 主キー: (region_id, ip_version)
 *
 * @property string      $region_id
 * @property int         $ip_version         4|6
 * @property string      $total_address_count NUMERIC(39,0)
 * @property string|null $last_allocation_date
 *
 * @property-read Region|null $region
 */
final class RegionStat extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%region_stat}}';
    }

    /** @inheritdoc */
    public function rules(): array
    {
        return [
            [['region_id', 'ip_version', 'total_address_count'], 'required'],
            [['region_id'], 'string', 'max' => 2],
            ['ip_version', 'in', 'range' => [4, 6]],
            ['total_address_count', 'match', 'pattern' => '/^\d+$/'],
            [['last_allocation_date'], 'safe'],
            [['region_id', 'ip_version'], 'unique',
                'targetAttribute' => ['region_id', 'ip_version'],
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
            'region_id'             => 'Region ID',
            'ip_version'            => 'IP Version',
            'total_address_count'   => 'Total Address Count',
            'last_allocation_date'  => 'Last Allocation Date',
        ];
    }

    public function getRegion(): ActiveQuery
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }
}