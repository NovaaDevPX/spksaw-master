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

  // Jika ALL → ambil semua data
  if ($period === "all") {
    $q = $db->query("
      SELECT a.id_alternative, b.name, a.id_criteria, a.value
      FROM saw_evaluations a
      JOIN saw_alternatives b ON b.id_alternative = a.id_alternative
      ORDER BY a.id_alternative, a.id_criteria
    ");
  } else {
    // Mode periode tertentu
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

  // Fixed scale
  $minScale = 1;
  $maxScale = 5;
  $range = $maxScale - $minScale; // = 4

  foreach ($values as $id_alt => $criteriaVals) {
    foreach ($criteriaVals as $id_crit => $xij) {

      // Bobot % -> desimal
      $wj = $bobot[$id_crit] / 100;

      // Jika cost
      if ($krit[$id_crit] === 'cost') {
        // (max - xij) / range * wj
        $r = (($maxScale - $xij) / $range) * $wj;
      } else {
        // benefit → (xij - min) / range * wj
        $r = (($xij - $minScale) / $range) * $wj;
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
      'name' => $alts[$id_alt],
      'nilai' => number_format($nilai, 3)
    ];
  }

  return $rank;
}
