<?php

/**
 * @copyright Copyright (C) 2021-2025
 * @license   MIT
 * @author    AIZAWA Hina
 */

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Table {{%allocation_block}}
 *
 * @property int         $id
 * @property string      $start_address  INET
 * @property string      $count          NUMERIC(39,0)  ← IPv6 /32 = 2^96
 * @property int         $ip_version     4|6
 * @property string|null $date
 * @property string      $region_id
 * @property string      $registry_id
 *
 * @property-read AllocationCidr[] $allocationCidrs
 * @property-read Region|null      $region
 * @property-read Registry|null    $registry
 */
final class AllocationBlock extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%allocation_block}}';
    }

    /** @inheritdoc */
    public function rules(): array
    {
        return [
            [['start_address', 'count', 'ip_version', 'registry_id', 'region_id'], 'required'],
            [['start_address'], 'string'],
            // NUMERIC を文字列で受け取りつつ整数性を担保
            ['count', 'match', 'pattern' => '/^\d+$/'],
            ['ip_version', 'in', 'range' => [4, 6]],
            [['date'], 'safe'],
            [['registry_id'], 'string', 'max' => 7],
            [['region_id'], 'string', 'max' => 2],
            [['start_address', 'ip_version'], 'unique', 'targetAttribute' => ['start_address', 'ip_version']],
            [['region_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => Region::class,
                'targetAttribute' => ['region_id' => 'id'],
            ],
            [['registry_id'], 'exist',
                'skipOnError' => true,
                'targetClass' => Registry::class,
                'targetAttribute' => ['registry_id' => 'id'],
            ],
        ];
    }

    /** @codeCoverageIgnore */
    public function attributeLabels(): array
    {
        return [
            'id'            => 'ID',
            'start_address' => 'Start Address',
            'count'         => 'Address Count',
            'ip_version'    => 'IP Version',
            'date'          => 'Date',
            'region_id'     => 'Region ID',
            'registry_id'   => 'Registry ID',
        ];
    }

    public function getAllocationCidrs(): ActiveQuery
    {
        return $this->hasMany(AllocationCidr::class, ['block_id' => 'id']);
    }

    public function getRegion(): ActiveQuery
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    public function getRegistry(): ActiveQuery
    {
        return $this->hasOne(Registry::class, ['id' => 'registry_id']);
    }
}