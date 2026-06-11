<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $strings = [
                'ui' => 20,
                'nohp' => 30,
                'kode_dept' => 5,
                'norek' => 10,
                'dept_name' => 15,
                'status_lama' => 2,
                'group' => 2,
                'unit' => 3,
                'unit2' => 3,
                'stat_agt' => 1,
                'pria' => 1,
                'ket' => 35,
            ];

            foreach ($strings as $column => $length) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->string($column, $length)->nullable();
                }
            }

            $dates = ['tg_msk', 'tgl_klr'];

            foreach ($dates as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->date($column)->nullable();
                }
            }

            $numerics = [
                'gaji' => 12,
                'limit_ppob' => 12,
                'limit_hutang' => 12,
                'pokok_ke' => 1,
                'tot_wajib' => 8,
                'tot_pokok' => 8,
                'tot_sbm' => 8,
                'tot_sjt' => 9,
                'shu_sim' => 9,
                'shu_toko' => 9,
                'tot_sjhu' => 9,
                'bungasbm' => 9,
                'bungaspw' => 9,
                'bungsim09' => 9,
                'jasa11' => 9,
                'tot_simp' => 9,
                'sisa_pin_u' => 12,
                'sisa_pin_b' => 12,
                'sisa_bkl' => 12,
                'sisa_p_ub' => 12,
                'tot_pin' => 12,
                'sisa_bri' => 10,
                'sisa_sdr' => 10,
                'sisa_btn' => 10,
                'sisa_bri2' => 10,
                'sisa_sdr2' => 10,
                'sisa_sdr3' => 10,
                'tot_bank' => 10,
                'minus' => 8,
                'ttpot' => 8,
                'sdr_ke' => 2,
                'pot_sdr' => 10,
                'bri_ke' => 3,
                'pot_bri' => 10,
                'btn_ke' => 3,
                'pot_btn' => 10,
                'pot_bri2' => 10,
                'bri2_ke' => 3,
                'vi_bni' => 10,
                'bni' => 10,
                'pot_sdr2' => 10,
                'sdr2_ke' => 2,
                'pot_sdr3' => 10,
                'sdr3_ke' => 2,
                'pot_mtr' => 10,
                'tsj' => 10,
                'pot_pokok' => 10,
                'pot_sjt' => 10,
                'pot_wajib' => 10,
                'pot_sbm' => 10,
                'pot_uang1' => 9,
                'pot_uang2' => 9,
                'pot_brgu1' => 8,
                'pot_brgu2' => 9,
                'pot_bub1' => 9,
                'pot_bub2' => 9,
                'pot_bu1' => 9,
                'pot_bu2' => 9,
                'pot_brg' => 9,
                'pot_uangb' => 9,
                'pot_uang' => 9,
                'pot_9pokok' => 9,
                'pot_beng' => 9,
                'beng_ke' => 2,
                'pot_tungb' => 9,
                'potbr1' => 9,
                'potbr2' => 9,
                'potbr3' => 9,
                'potbr4' => 9,
                'potbr5' => 9,
                'potbr6' => 9,
                'angs1' => 3,
                'angs2' => 3,
                'angs3' => 3,
                'angs4' => 3,
                'angs5' => 3,
                'uang_ke1' => 3,
                'uang_ke2' => 3,
                'uangb_ke1' => 3,
                'uangb_ke2' => 3,
                'sisa_uang1' => 10,
                'sisa_uang2' => 10,
                'sisa_ub1' => 10,
                'sisa_ub2' => 10,
                'sisa_beng' => 10,
                'no_urt' => 4,
                'tot_pot' => 9,
                'pot_kop' => 9,
                'pot_bank' => 9,
                'pot_simp' => 9,
                'jum' => 9,
                'sisa_brg1' => 10,
                'sisa_brg2' => 10,
                'sisa_brg3' => 10,
                'sisa_brg4' => 10,
                'sisa_brg5' => 10,
                'sisa_brg6' => 10,
                'sisa_brg7' => 10,
                'tali' => 10,
                'harian' => 12,
                'shu' => 10,
                'shu1' => 10,
                'spsw25' => 9,
            ];

            foreach ($numerics as $column => $precision) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->decimal($column, $precision, 0)->nullable()->default(0);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'kode_dept',
            'norek',
            'dept_name',
            'status_lama',
            'group',
            'unit',
            'unit2',
            'pokok_ke',
            'tot_wajib',
            'tot_pokok',
            'tot_sbm',
            'tot_sjt',
            'shu_sim',
            'shu_toko',
            'tot_sjhu',
            'bungasbm',
            'bungaspw',
            'bungsim09',
            'jasa11',
            'tot_simp',
            'sisa_pin_u',
            'sisa_pin_b',
            'sisa_bkl',
            'sisa_p_ub',
            'tot_pin',
            'sisa_bri',
            'sisa_sdr',
            'sisa_btn',
            'sisa_bri2',
            'sisa_sdr2',
            'sisa_sdr3',
            'tot_bank',
            'minus',
            'ttpot',
            'stat_agt',
            'pria',
            'tg_msk',
            'sdr_ke',
            'pot_sdr',
            'bri_ke',
            'pot_bri',
            'btn_ke',
            'pot_btn',
            'pot_bri2',
            'bri2_ke',
            'vi_bni',
            'bni',
            'pot_sdr2',
            'sdr2_ke',
            'pot_sdr3',
            'sdr3_ke',
            'pot_mtr',
            'tsj',
            'pot_pokok',
            'pot_sjt',
            'pot_wajib',
            'pot_sbm',
            'pot_uang1',
            'pot_uang2',
            'pot_brgu1',
            'pot_brgu2',
            'pot_bub1',
            'pot_bub2',
            'pot_bu1',
            'pot_bu2',
            'pot_brg',
            'pot_uangb',
            'pot_uang',
            'pot_9pokok',
            'pot_beng',
            'beng_ke',
            'pot_tungb',
            'potbr1',
            'potbr2',
            'potbr3',
            'potbr4',
            'potbr5',
            'potbr6',
            'angs1',
            'angs2',
            'angs3',
            'angs4',
            'angs5',
            'uang_ke1',
            'uang_ke2',
            'uangb_ke1',
            'uangb_ke2',
            'sisa_uang1',
            'sisa_uang2',
            'sisa_ub1',
            'sisa_ub2',
            'sisa_beng',
            'no_urt',
            'tot_pot',
            'pot_kop',
            'pot_bank',
            'pot_simp',
            'jum',
            'sisa_brg1',
            'sisa_brg2',
            'sisa_brg3',
            'sisa_brg4',
            'sisa_brg5',
            'sisa_brg6',
            'sisa_brg7',
            'tali',
            'tgl_klr',
            'harian',
            'shu',
            'shu1',
            'spsw25',
            'ket',
        ];

        Schema::table('users', function (Blueprint $table) use ($columns) {
            $existingColumns = array_filter($columns, fn ($column) => Schema::hasColumn('users', $column));

            if ($existingColumns) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
