<?php
require "include/conn.php";

function getPeriodList($db)
{
  $list = [];
  $q = $db->query("SELECT DISTINCT period FROM saw_evaluations ORDER BY period DESC");
  while ($r = $q->fetch_object()) {
    $list[] = $r->period;
  }
  return $list;
}

function getKriteria($db)
{
  $krit = [];
  $bobot = [];
  $q = $db->query("SELECT id_criteria, attribute, weight FROM saw_criterias ORDER BY id_criteria");
  while ($r = $q->fetch_object()) {
    $krit[$r->id_criteria] = $r->attribute;
    $bobot[$r->id_criteria] = $r->weight;
  }
  return [$krit, $bobot];
}

function getEvaluasi($db, $period)
{
  $values = [];
  $alts = [];

  if ($period === "all") {
    $q = $db->query("
            SELECT a.id_alternative, b.name, a.id_criteria, a.value
            FROM saw_evaluations a
            JOIN saw_alternatives b ON b.id_alternative = a.id_alternative
            ORDER BY a.id_alternative, a.id_criteria
        ");
  } else {
    $q = $db->query("
            SELECT a.id_alternative, b.name, a.id_criteria, a.value
            FROM saw_evaluations a
            JOIN saw_alternatives b ON b.id_alternative = a.id_alternative
            WHERE a.period = '$period'
            ORDER BY a.id_alternative, a.id_criteria
        ");
  }

  while ($r = $q->fetch_object()) {
    $alts[$r->id_alternative] = $r->name;
    $values[$r->id_alternative][$r->id_criteria] = $r->value;
  }

  return [$values, $alts];
}

function hitungNormalisasi($db, $values, $krit, $bobot)
{
  $R = [];
  $maxScale = 5;

  foreach ($values as $id_alt => $criteriaVals) {
    foreach ($criteriaVals as $id_crit => $xij) {
      $wj = $bobot[$id_crit] / 100;
      if ($krit[$id_crit] === "cost") {
        $r = (($maxScale - $xij + 1) / $maxScale) * $wj;
      } else {
        $r = ($xij / $maxScale) * $wj;
      }
      $R[$id_alt][$id_crit] = $r;
    }
  }

  return $R;
}

function hitungNilaiAkhir($R)
{
  $P = [];
  foreach ($R as $id_alt => $nilai) {
    $P[$id_alt] = array_sum($nilai);
  }
  return $P;
}

function perangkingan($P, $alts)
{
  arsort($P);
  $rank = [];
  $no = 1;
  foreach ($P as $id_alt => $nilai) {
    $rank[] = [
      'ranking' => $no++,
      'id' => $id_alt,
      'name' => $alts[$id_alt],
      'nilai' => number_format($nilai, 3)
    ];
  }
  return $rank;
}

/* =========================================
   Fungsi narasi ranking dengan BREAK
========================================= */
function buatNarasiRankingBreak($ranking, $bulan, $tahun)
{
  if (empty($ranking)) {
    return [
      'narasi_1' => "Belum ada data penilaian pada periode {$bulan} {$tahun}.",
      'narasi_lanjutan' => "",
      'rank1' => null,
      'rank_lanjutan' => []
    ];
  }

  // Peringkat 1
  $rank1 = $ranking[0];
  $narasi1 = "Pada periode {$bulan} {$tahun}, peringkat pertama diraih oleh {$rank1['name']} dengan nilai akhir {$rank1['nilai']}.";

  // Peringkat selanjutnya
  $narasiLanjutan = "Peringkat selanjutnya adalah sebagai berikut:\n\n";
  $rankLanjutan = [];
  for ($i = 1; $i < count($ranking); $i++) {
    $r = $ranking[$i];
    $narasiLanjutan .= "Peringkat ke-{$r['ranking']} ditempati oleh {$r['name']} dengan nilai {$r['nilai']}.\n";
    $rankLanjutan[] = $r;
  }

  return [
    'narasi_1' => $narasi1,
    'narasi_lanjutan' => $narasiLanjutan,
    'rank1' => $rank1,
    'rank_lanjutan' => $rankLanjutan
  ];
}

// Ambil nilai per kriteria untuk alternatif tertentu
function getNilaiPerKriteria($values, $id_alt, $krit)
{
  $data = [];
  foreach ($krit as $id_crit => $attr) {
    $data[$id_crit] = $values[$id_alt][$id_crit] ?? 0;
  }
  return $data;
}
