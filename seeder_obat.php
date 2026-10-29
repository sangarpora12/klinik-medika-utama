<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

$obat = [
  ['Paracetamol 500mg', 'Analgesik', 'tablet', 200, 5000, 'Obat pereda demam dan nyeri ringan'],
  ['Ibuprofen 400mg', 'Antiinflamasi', 'tablet', 150, 8000, 'Antiinflamasi non-steroid'],
  ['Amoxicillin 500mg', 'Antibiotik', 'kapsul', 100, 12000, 'Antibiotik spektrum luas'],
  ['Cefixime 200mg', 'Antibiotik', 'kapsul', 80, 25000, 'Antibiotik sefalosporin'],
  ['Azithromycin 500mg', 'Antibiotik', 'tablet', 90, 35000, 'Antibiotik makrolida'],
  ['Metformin 500mg', 'Antidiabetes', 'tablet', 180, 7000, 'Obat diabetes tipe 2'],
  ['Glibenclamide 5mg', 'Antidiabetes', 'tablet', 160, 4000, 'Obat diabetes golongan sulfonilurea'],
  ['Omeprazole 20mg', 'Antasida', 'kapsul', 220, 10000, 'Penghambat pompa proton untuk maag'],
  ['Ranitidine 150mg', 'Antasida', 'tablet', 200, 6000, 'Antagonis H2 untuk tukak lambung'],
  ['Amlodipine 10mg', 'Antihipertensi', 'tablet', 140, 9000, 'Penghambat saluran kalsium'],
  ['Captopril 25mg', 'Antihipertensi', 'tablet', 130, 7000, 'ACE inhibitor untuk hipertensi'],
  ['Simvastatin 20mg', 'Antikolesterol', 'tablet', 120, 11000, 'Statin untuk menurunkan kolesterol'],
  ['Atorvastatin 20mg', 'Antikolesterol', 'tablet', 110, 15000, 'Statin generasi terbaru'],
  ['Salbutamol Inhaler', 'Pernapasan', 'botol', 50, 45000, 'Bronkodilator untuk asma'],
  ['Dexamethasone 0.5mg', 'Kortikosteroid', 'tablet', 170, 3000, 'Antiinflamasi kortikosteroid'],
  ['Prednisone 5mg', 'Kortikosteroid', 'tablet', 140, 3500, 'Kortikosteroid sistemik'],
  ['Diazepam 5mg', 'Sedatif', 'tablet', 80, 8000, 'Obat penenang dan antikejang'],
  ['Furosemide 40mg', 'Diuretik', 'tablet', 100, 5000, 'Diuretik untuk edema dan hipertensi'],
  ['Spironolactone 25mg', 'Diuretik', 'tablet', 90, 7500, 'Diuretik hemat kalium'],
  ['Bisoprolol 5mg', 'Antihipertensi', 'tablet', 95, 8500, 'Beta blocker untuk hipertensi'],
  ['Warfarin 5mg', 'Antikoagulan', 'tablet', 60, 6000, 'Pengencer darah'],
  ['Aspirin 80mg', 'Antiplatelet', 'tablet', 250, 2000, 'Pengencer darah dosis rendah'],
  ['Diphenhydramine 25mg', 'Antihistamin', 'kapsul', 130, 4000, 'Obat alergi generasi pertama'],
  ['Cetirizine 10mg', 'Antihistamin', 'tablet', 200, 5000, 'Obat alergi generasi kedua'],
  ['Loratadine 10mg', 'Antihistamin', 'tablet', 180, 6000, 'Obat alergi non-mengantuk'],
  ['Mefenamic Acid 500mg', 'Analgesik', 'kapsul', 160, 7000, 'Obat nyeri haid dan sakit gigi'],
  ['Domperidone 10mg', 'Antiemetik', 'tablet', 120, 6000, 'Obat mual dan muntah'],
  ['Ondansetron 4mg', 'Antiemetik', 'tablet', 75, 12000, 'Anti mual kuat'],
  ['Bromhexine 8mg', 'Mukolitik', 'tablet', 140, 4000, 'Pengencer dahak'],
  ['CTM 4mg', 'Antihistamin', 'tablet', 190, 1500, 'Obat alergi dan pilek'],
  ['Dextromethorphan 15mg', 'Antitusif', 'tablet', 110, 5500, 'Obat batuk kering'],
  ['Multivitamin', 'Suplemen', 'tablet', 300, 10000, 'Suplemen multivitamin harian'],
  ['Vitamin C 500mg', 'Suplemen', 'tablet', 280, 8000, 'Vitamin C imunitas'],
  ['Vitamin B Kompleks', 'Suplemen', 'tablet', 250, 9500, 'Vitamin B lengkap'],
  ['Zinc 20mg', 'Suplemen', 'tablet', 220, 7000, 'Suplemen seng untuk imunitas'],
  ['Betadine 30ml', 'Antiseptik', 'botol', 80, 18000, 'Larutan antiseptik luka'],
  ['Gentamicin Salep', 'Antibiotik', 'tube', 65, 15000, 'Salep antibiotik kulit'],
  ['Miconazole Krim', 'Antijamur', 'tube', 55, 12000, 'Krim antijamur kulit'],
  ['Albendazole 400mg', 'Antelmintik', 'tablet', 70, 8000, 'Obat cacingan'],
  ['Hydrocortisone Krim', 'Kortikosteroid', 'tube', 45, 20000, 'Krim anti radang kulit'],
  ['Chloramphenicol Tetes', 'Antibiotik', 'botol', 40, 15000, 'Tetes mata antibiotik'],
  ['Allopurinol 100mg', 'Antigout', 'tablet', 85, 6000, 'Penurun asam urat'],
  ['Aciclovir 400mg', 'Antivirus', 'tablet', 60, 20000, 'Obat herpes dan cacar'],
  ['Kombinasi Obat Batuk', 'Batuk', 'botol', 70, 25000, 'Obat batuk kombinasi sirup'],
  ['Antasida Sirup', 'Antasida', 'botol', 90, 15000, 'Obat maag cair'],
  ['Gerbinafin Krim', 'Antijamur', 'tube', 50, 18000, 'Krim jamur kadas kurap'],
  ['Ketoconazole 200mg', 'Antijamur', 'tablet', 60, 15000, 'Obat jamur sistemik'],
  ['Paracetamol Sirup', 'Analgesik', 'botol', 85, 20000, 'Obat demam anak sirup'],
  ['Attapulgite 600mg', 'Antidiare', 'tablet', 100, 5000, 'Obat diare'],
  ['Loperamide 2mg', 'Antidiare', 'kapsul', 90, 7000, 'Obat diare akut'],
];

$stmt = $db->prepare('INSERT INTO obat (nama_obat, kategori, satuan, stok, harga, keterangan) VALUES (?,?,?,?,?,?)');
$total = 0;
foreach ($obat as $o) {
  try {
    $stmt->execute($o);
    $total++;
  } catch (Exception $e) {
    echo 'Skip: ' . $o[0] . ' -> ' . $e->getMessage() . PHP_EOL;
  }
}
echo "Berhasil menambah $total jenis obat." . PHP_EOL;