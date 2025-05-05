public function safeUp()
{
    // allocation_block
    $this->addColumn('{{%allocation_block}}', 'ip_version', 'SMALLINT NOT NULL DEFAULT 4');
    $this->alterColumn('{{%allocation_block}}', 'count', 'NUMERIC(39,0)');

    // allocation_cidr
    $this->addColumn('{{%allocation_cidr}}', 'ip_version', 'SMALLINT NOT NULL DEFAULT 4');
    $this->createIndex(
        'allocation_cidr__ver_cidr__gist',
        '{{%allocation_cidr}}',
        ['ip_version', 'cidr'],
        false,
        'USING gist (cidr inet_ops)'
    );

    // merged_cidr
    $this->addColumn('{{%merged_cidr}}', 'ip_version', 'SMALLINT NOT NULL DEFAULT 4');
    $this->createIndex(
        'merged_cidr__ver_region_cidr__gist',
        '{{%merged_cidr}}',
        ['ip_version','region_id','cidr'],
        true,
        'USING gist (cidr inet_ops)'
    );

    // region_stat
    $this->renameTable('{{%region_stat}}', '{{%region_stat_old}}');
    $this->createTable('{{%region_stat}}', [
        'region_id' => 'CHAR(2) NOT NULL REFERENCES {{%region}}([[id]])',
        'ip_version' => 'SMALLINT NOT NULL',
        'total_address_count' => 'NUMERIC(39,0) NOT NULL',
        'last_allocation_date' => 'DATE NULL',
        'PRIMARY KEY ([[region_id]], [[ip_version]])',
    ]);
    $this->execute('INSERT INTO {{%region_stat}} (region_id, ip_version, total_address_count, last_allocation_date)
                    SELECT region_id, 4, total_address_count, last_allocation_date FROM {{%region_stat_old}}');
    $this->dropTable('{{%region_stat_old}}');
}