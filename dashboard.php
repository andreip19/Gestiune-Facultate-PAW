<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';
$mesaj = '';
$rol = $_SESSION['rol']; 
$id_curent = $_SESSION['user_id'];

if (isset($_GET['logout'])) {
    session_destroy(); header("Location: index.php"); exit;
}

if ($rol === 'admin') {
    $pagina_curenta = isset($_GET['page']) ? $_GET['page'] : 'studenti';
} elseif ($rol === 'profesor') {
    $pagina_curenta = isset($_GET['page']) ? $_GET['page'] : 'materiile_mele';
} elseif ($rol === 'student') {
    $pagina_curenta = isset($_GET['page']) ? $_GET['page'] : 'situatia_mea';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($rol === 'admin') {
        if ($_POST['action'] === 'adauga_student') {
            $matricol = trim($_POST['matricol']); $nume = trim($_POST['nume']); $prenume = trim($_POST['prenume']);
            $specializare = $_POST['specializare']; $an = $_POST['an_studiu']; $grupa = trim($_POST['grupa']); $finantare = $_POST['finantare'];
            $user_gen = strtolower($nume . '.' . $prenume); $pass = password_hash('1234', PASSWORD_BCRYPT);
            try {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO utilizatori (username, parola, rol) VALUES (?, ?, 'student')")->execute([$user_gen, $pass]);
                $noul_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO studenti (id_student, matricol, nume, prenume, specializare, an_studiu, grupa, finantare) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")->execute([$noul_id, $matricol, $nume, $prenume, $specializare, $an, $grupa, $finantare]);
                $pdo->prepare("INSERT INTO istoric (id_utilizator, actiune, detalii) VALUES (?, 'ADAUGARE STUDENT', ?)")->execute([$id_curent, "A adaugat studentul: $nume $prenume"]);
                $pdo->commit();
                $mesaj = "<div class='alert alert-success d-print-none'>Student adăugat cu succes!</div>";
            } catch (PDOException $e) { $pdo->rollBack(); $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
        elseif ($_POST['action'] === 'modifica_student') {
            $id_stud = $_POST['id_student']; $nume = trim($_POST['nume']); $prenume = trim($_POST['prenume']);
            $specializare = $_POST['specializare']; $an = $_POST['an_studiu']; $grupa = trim($_POST['grupa']); $finantare = $_POST['finantare'];
            try {
                $pdo->prepare("UPDATE studenti SET nume=?, prenume=?, specializare=?, an_studiu=?, grupa=?, finantare=? WHERE id_student=?")->execute([$nume, $prenume, $specializare, $an, $grupa, $finantare, $id_stud]);
                $pdo->prepare("INSERT INTO istoric (id_utilizator, actiune, detalii) VALUES (?, 'MODIFICARE STUDENT', ?)")->execute([$id_curent, "A modificat datele studentului ID $id_stud"]);
                $mesaj = "<div class='alert alert-success d-print-none'>Date actualizate!</div>";
            } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
        elseif ($_POST['action'] === 'sterge_student') {
            try {
                $pdo->prepare("DELETE FROM utilizatori WHERE id_utilizator = ?")->execute([$_POST['id_utilizator']]);
                $mesaj = "<div class='alert alert-warning d-print-none'>Student șters!</div>";
            } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
    }
    
    elseif ($rol === 'profesor') {
        if ($_POST['action'] === 'adauga_nota') {
            $id_stud = $_POST['id_student'];
            $id_disc = $_POST['id_disciplina'];
            $valoare = $_POST['valoare'];
            $data_n = date('Y-m-d');
            try {
                $check = $pdo->prepare("SELECT COUNT(*) FROM profesori_discipline pd JOIN studenti s ON pd.id_disciplina = ? WHERE pd.id_profesor = ? AND s.id_student = ? AND s.an_studiu = (SELECT an_studiu FROM discipline WHERE id_disciplina = ?)");
                $check->execute([$id_disc, $id_curent, $id_stud, $id_disc]);
                if ($check->fetchColumn() > 0) {
                    $pdo->prepare("INSERT INTO note (id_student, id_disciplina, id_profesor, valoare, data_notarii) VALUES (?, ?, ?, ?, ?)")->execute([$id_stud, $id_disc, $id_curent, $valoare, $data_n]);
                    $pdo->prepare("INSERT INTO istoric (id_utilizator, actiune, detalii) VALUES (?, 'ADAUGARE NOTA', ?)")->execute([$id_curent, "A adaugat nota $valoare studentului ID $id_stud la disciplina ID $id_disc"]);
                    $mesaj = "<div class='alert alert-success d-print-none'>Notă adăugată cu succes!</div>";
                } else {
                    $mesaj = "<div class='alert alert-danger d-print-none'>Eroare de validare! Studentul nu este în anul corespunzător materiei sau nu sunteți titular.</div>";
                }
            } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
        elseif ($_POST['action'] === 'modifica_nota') {
            $id_nota = $_POST['id_nota'];
            $valoare = $_POST['valoare'];
            try {
                $pdo->prepare("UPDATE note SET valoare=?, data_notarii=? WHERE id_nota=? AND id_profesor=?")->execute([$valoare, date('Y-m-d'), $id_nota, $id_curent]);
                $pdo->prepare("INSERT INTO istoric (id_utilizator, actiune, detalii) VALUES (?, 'MODIFICARE NOTA', ?)")->execute([$id_curent, "A modificat nota ID $id_nota la valoarea $valoare"]);
                $mesaj = "<div class='alert alert-success d-print-none'>Nota a fost actualizată!</div>";
            } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
        elseif ($_POST['action'] === 'sterge_nota') {
            try {
                $pdo->prepare("DELETE FROM note WHERE id_nota=? AND id_profesor=?")->execute([$_POST['id_nota'], $id_curent]);
                $pdo->prepare("INSERT INTO istoric (id_utilizator, actiune, detalii) VALUES (?, 'STERGERE NOTA', ?)")->execute([$id_curent, "A sters nota ID " . $_POST['id_nota']]);
                $mesaj = "<div class='alert alert-warning d-print-none'>Notă ștearsă!</div>";
            } catch (PDOException $e) { $mesaj = "<div class='alert alert-danger d-print-none'>Eroare: " . $e->getMessage() . "</div>"; }
        }
    }
}

$date_tabel = [];
$istoric_date = [];
$profil_student = [];
$disciplina_curenta = [];
$statistici_note = ['sub_5' => 0, '5_6' => 0, '7_8' => 0, '9_10' => 0];
$data_start = isset($_GET['data_start']) ? $_GET['data_start'] : date('Y-01-01');
$data_end = isset($_GET['data_end']) ? $_GET['data_end'] : date('Y-m-d');

try {
    if ($rol === 'admin') {
        if ($pagina_curenta === 'studenti') {
            $sql = "SELECT id_student, matricol, nume, prenume, specializare, an_studiu, grupa, finantare FROM studenti WHERE 1=1";
            $parametri = [];
            if (!empty($_GET['search'])) { $sql .= " AND (nume LIKE ? OR prenume LIKE ? OR matricol LIKE ?)"; $c = '%'.$_GET['search'].'%'; array_push($parametri, $c, $c, $c); }
            if (!empty($_GET['specializare'])) { $sql .= " AND specializare = ?"; $parametri[] = $_GET['specializare']; }
            if (!empty($_GET['an_studiu'])) { $sql .= " AND an_studiu = ?"; $parametri[] = $_GET['an_studiu']; }
            $sql .= " ORDER BY nume ASC";
            $stmt = $pdo->prepare($sql); $stmt->execute($parametri); $date_tabel = $stmt->fetchAll();
        } 
        elseif ($pagina_curenta === 'profesori') {
            $stmt = $pdo->query("SELECT id_profesor, nume, prenume, departament FROM profesori ORDER BY nume ASC");
            $date_tabel = $stmt->fetchAll();
        }
        elseif ($pagina_curenta === 'discipline') {
            $stmt = $pdo->query("SELECT id_disciplina, denumire, an_studiu, semestru, credite FROM discipline ORDER BY denumire ASC");
            $date_tabel = $stmt->fetchAll();
        }
        elseif ($pagina_curenta === 'rapoarte') {
            $stmt_raport = $pdo->prepare("SELECT valoare FROM note WHERE data_notarii BETWEEN ? AND ?");
            $stmt_raport->execute([$data_start, $data_end]);
            $toate_notele = $stmt_raport->fetchAll(PDO::FETCH_COLUMN);
            foreach($toate_notele as $nota) {
                if($nota < 5) $statistici_note['sub_5']++;
                elseif($nota <= 6) $statistici_note['5_6']++;
                elseif($nota <= 8) $statistici_note['7_8']++;
                else $statistici_note['9_10']++;
            }
        }
        elseif ($pagina_curenta === 'istoric') {
             $stmt = $pdo->query("SELECT i.id_istoric, u.username, i.actiune, i.detalii, i.data_ora FROM istoric i LEFT JOIN utilizatori u ON i.id_utilizator = u.id_utilizator ORDER BY i.data_ora DESC LIMIT 50");
             $istoric_date = $stmt->fetchAll();
        }
    } 
    elseif ($rol === 'profesor') {
        if ($pagina_curenta === 'materiile_mele') {
            $stmt = $pdo->prepare("SELECT d.id_disciplina, d.denumire, d.an_studiu, d.semestru FROM discipline d JOIN profesori_discipline pd ON d.id_disciplina = pd.id_disciplina WHERE pd.id_profesor = ?");
            $stmt->execute([$id_curent]); $date_tabel = $stmt->fetchAll();
        }
        elseif ($pagina_curenta === 'catalog' && isset($_GET['id_disciplina'])) {
            $id_disc = $_GET['id_disciplina'];
            $stmt_d = $pdo->prepare("SELECT denumire, an_studiu FROM discipline WHERE id_disciplina = ?");
            $stmt_d->execute([$id_disc]);
            $disciplina_curenta = $stmt_d->fetch();
            
            $sql = "SELECT s.id_student, s.matricol, s.nume, s.prenume, s.grupa, n.id_nota, n.valoare, n.data_notarii FROM studenti s LEFT JOIN note n ON s.id_student = n.id_student AND n.id_disciplina = :id_disc WHERE s.an_studiu = :an";
            $params = [':id_disc' => $id_disc, ':an' => $disciplina_curenta['an_studiu']];
            
            if (isset($_GET['filtru']) && $_GET['filtru'] === 'restante') {
                $sql .= " AND n.valoare < 5";
            }
            if (isset($_GET['sort'])) {
                if ($_GET['sort'] === 'asc') $sql .= " ORDER BY n.valoare ASC";
                elseif ($_GET['sort'] === 'desc') $sql .= " ORDER BY n.valoare DESC";
                else $sql .= " ORDER BY s.nume ASC";
            } else {
                $sql .= " ORDER BY s.nume ASC";
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $date_tabel = $stmt->fetchAll();
        }
    }
    elseif ($rol === 'student') {
        if ($pagina_curenta === 'situatia_mea') {
            $stmt_an = $pdo->prepare("SELECT an_studiu FROM studenti WHERE id_student = ?");
            $stmt_an->execute([$id_curent]);
            $anul_meu = $stmt_an->fetchColumn();

            $sql = "SELECT d.denumire, d.credite, n.valoare, n.data_notarii FROM discipline d LEFT JOIN note n ON d.id_disciplina = n.id_disciplina AND n.id_student = :id_stud WHERE d.an_studiu = :an";
            $params = [':id_stud' => $id_curent, ':an' => $anul_meu];
            
            if (isset($_GET['filtru']) && $_GET['filtru'] === 'restante') {
                $sql .= " AND n.valoare < 5";
            }
            if (isset($_GET['sort'])) {
                if ($_GET['sort'] === 'asc') $sql .= " ORDER BY n.valoare ASC";
                elseif ($_GET['sort'] === 'desc') $sql .= " ORDER BY n.valoare DESC";
                else $sql .= " ORDER BY d.denumire ASC";
            } else {
                $sql .= " ORDER BY d.denumire ASC";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params); $date_tabel = $stmt->fetchAll();
        }
        elseif ($pagina_curenta === 'profil') {
            $stmt = $pdo->prepare("SELECT matricol, nume, prenume, specializare, an_studiu, grupa, finantare FROM studenti WHERE id_student = ?");
            $stmt->execute([$id_curent]);
            $profil_student = $stmt->fetch();
        }
    }
} catch (PDOException $e) { die("Eroare la extragerea datelor: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Facultate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .d-print-none, nav, .list-group, form, button { display: none !important; }
            .col-md-10 { width: 100% !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm d-print-none">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-university me-2"></i>Gestiune Facultate</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3">Logat ca: <strong class="text-warning"><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo strtoupper($rol); ?>)</strong></span>
                <a href="dashboard.php?logout=1" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i> Ieșire</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            
            <div class="col-md-2 mb-4 d-print-none">
                <div class="list-group shadow-sm">
                    <?php if ($rol === 'admin'): ?>
                        <a href="dashboard.php?page=studenti" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'studenti' ? 'active' : ''; ?>"><i class="fas fa-user-graduate me-2"></i> Studenți</a>
                        <a href="dashboard.php?page=profesori" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'profesori' ? 'active' : ''; ?>"><i class="fas fa-chalkboard-teacher me-2"></i> Profesori</a>
                        <a href="dashboard.php?page=discipline" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'discipline' ? 'active' : ''; ?>"><i class="fas fa-book me-2"></i> Discipline</a>
                        <a href="dashboard.php?page=rapoarte" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'rapoarte' ? 'active' : ''; ?>"><i class="fas fa-chart-pie me-2"></i> Rapoarte</a>
                        <a href="dashboard.php?page=istoric" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'istoric' ? 'active' : ''; ?>"><i class="fas fa-history me-2"></i> Istoric Activitate</a>
                    <?php elseif ($rol === 'profesor'): ?>
                        <a href="dashboard.php?page=materiile_mele" class="list-group-item list-group-item-action <?php echo ($pagina_curenta == 'materiile_mele' || $pagina_curenta == 'catalog') ? 'active' : ''; ?>"><i class="fas fa-book-open me-2"></i> Materiile Mele</a>
                    <?php elseif ($rol === 'student'): ?>
                        <a href="dashboard.php?page=situatia_mea" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'situatia_mea' ? 'active' : ''; ?>"><i class="fas fa-graduation-cap me-2"></i> Notele Mele</a>
                        <a href="dashboard.php?page=profil" class="list-group-item list-group-item-action <?php echo $pagina_curenta == 'profil' ? 'active' : ''; ?>"><i class="fas fa-user me-2"></i> Profilul Meu</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-10">
                <?php echo $mesaj; ?>
                
                <?php if ($rol === 'student' && $pagina_curenta === 'profil'): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user me-2"></i>Date Administrative</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Nume și Prenume:</strong> <?php echo htmlspecialchars($profil_student['nume'] . ' ' . $profil_student['prenume']); ?></li>
                                <li class="list-group-item"><strong>Număr Matricol:</strong> <?php echo htmlspecialchars($profil_student['matricol']); ?></li>
                                <li class="list-group-item"><strong>Specializare:</strong> <?php echo htmlspecialchars($profil_student['specializare']); ?></li>
                                <li class="list-group-item"><strong>An Studiu:</strong> <?php echo htmlspecialchars($profil_student['an_studiu']); ?></li>
                                <li class="list-group-item"><strong>Grupa:</strong> <?php echo htmlspecialchars($profil_student['grupa']); ?></li>
                                <li class="list-group-item"><strong>Formă Finanțare:</strong> <span class="badge bg-<?php echo $profil_student['finantare'] == 'buget' ? 'success' : 'danger'; ?>"><?php echo ucfirst(htmlspecialchars($profil_student['finantare'])); ?></span></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($pagina_curenta !== 'rapoarte' && $pagina_curenta !== 'istoric' && $pagina_curenta !== 'profil'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">
                            <?php 
                                if($rol==='admin') echo "Panou Administrator - " . ucfirst($pagina_curenta);
                                if($rol==='profesor' && $pagina_curenta==='materiile_mele') echo "Materiile Mele";
                                if($rol==='profesor' && $pagina_curenta==='catalog') echo "Catalog: " . htmlspecialchars($disciplina_curenta['denumire']);
                                if($rol==='student') echo "Situație Școlară Curentă";
                            ?>
                        </h5>
                        
                        <div class="d-print-none">
                            <?php if ($rol === 'admin'): ?>
                                <a href="export.php?tabel=<?php echo $pagina_curenta; ?>" class="btn btn-success btn-sm me-2"><i class="fas fa-file-excel"></i> Export Excel</a>
                                <button onclick="window.print()" class="btn btn-danger btn-sm me-2"><i class="fas fa-file-pdf"></i> Salvează PDF</button>
                                <?php if ($pagina_curenta === 'studenti'): ?>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adaugaStudentModal"><i class="fas fa-plus"></i> Adaugă Student</button>
                                <?php endif; ?>
                            <?php elseif ($rol === 'profesor' && $pagina_curenta === 'catalog'): ?>
                                <button onclick="window.print()" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> Exportă Catalog PDF</button>
                            <?php elseif ($rol === 'student'): ?>
                                <button onclick="window.print()" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> Descarcă Situație PDF</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($rol === 'admin' && $pagina_curenta === 'studenti'): ?>
                        <form method="GET" action="dashboard.php" class="row g-3 mb-4 bg-light p-3 rounded border align-items-end d-print-none">
                            <input type="hidden" name="page" value="studenti">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Căutare Nume/Matricol</label>
                                <input type="text" class="form-control form-control-sm" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Specializare</label>
                                <select class="form-select form-select-sm" name="specializare">
                                    <option value="">Toate</option>
                                    <option value="CTI" <?php echo (isset($_GET['specializare']) && $_GET['specializare']=='CTI')?'selected':''; ?>>CTI</option>
                                    <option value="AIA" <?php echo (isset($_GET['specializare']) && $_GET['specializare']=='AIA')?'selected':''; ?>>AIA</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="fas fa-filter"></i> Filtrează</button>
                                <a href="dashboard.php?page=studenti" class="btn btn-outline-secondary btn-sm w-100 mt-1">Resetează Filtre</a>
                            </div>
                        </form>
                        <?php endif; ?>

                        <?php if (($rol === 'profesor' && $pagina_curenta === 'catalog') || $rol === 'student'): ?>
                        <form method="GET" action="dashboard.php" class="row g-3 mb-4 bg-light p-3 rounded border align-items-end d-print-none">
                            <input type="hidden" name="page" value="<?php echo $pagina_curenta; ?>">
                            <?php if($rol === 'profesor') { echo '<input type="hidden" name="id_disciplina" value="'.htmlspecialchars($_GET['id_disciplina']).'">'; } ?>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Filtru Note</label>
                                <select class="form-select form-select-sm" name="filtru">
                                    <option value="">Toate Notele</option>
                                    <option value="restante" <?php echo (isset($_GET['filtru']) && $_GET['filtru']=='restante')?'selected':''; ?>>Doar Restanțe (Sub 5) / Neacordate</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Ordonare</label>
                                <select class="form-select form-select-sm" name="sort">
                                    <option value="">Alfabetic</option>
                                    <option value="desc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='desc')?'selected':''; ?>>Note Descrescător</option>
                                    <option value="asc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='asc')?'selected':''; ?>>Note Crescător</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="fas fa-sort"></i> Aplică</button>
                                <a href="dashboard.php?page=<?php echo $pagina_curenta; ?><?php echo ($rol==='profesor')?'&id_disciplina='.$_GET['id_disciplina']:''; ?>" class="btn btn-outline-secondary btn-sm w-100 mt-1">Resetează</a>
                            </div>
                        </form>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <?php if ($rol === 'admin'): ?>
                                        <?php if ($pagina_curenta === 'studenti'): ?>
                                            <tr><th>Nr. Matricol</th><th>Nume Complet</th><th>Spec.</th><th>An</th><th>Grupa</th><th>Finanțare</th><th class="text-center d-print-none">Acțiuni</th></tr>
                                        <?php elseif ($pagina_curenta === 'profesori'): ?>
                                            <tr><th>Nume Complet</th><th>Departament</th></tr>
                                        <?php elseif ($pagina_curenta === 'discipline'): ?>
                                            <tr><th>Denumire Disciplină</th><th>An Studiu</th><th>Semestru</th><th>Credite</th></tr>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($rol === 'profesor'): ?>
                                        <?php if ($pagina_curenta === 'materiile_mele'): ?>
                                            <tr><th>Materie</th><th>An Studiu</th><th>Semestru</th><th class="text-center">Catalog</th></tr>
                                        <?php elseif ($pagina_curenta === 'catalog'): ?>
                                            <tr><th>Nr. Matricol</th><th>Nume Student</th><th>Grupa</th><th class="text-center">Nota Finală</th><th class="text-center d-print-none">Operațiuni</th></tr>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($rol === 'student'): ?>
                                        <tr><th>Disciplină</th><th>Credite</th><th class="text-center">Notă Obținută</th><th>Data Notării</th></tr>
                                    <?php endif; ?>
                                </thead>
                                <tbody>
                                    <?php if (count($date_tabel) > 0): ?>
                                        <?php foreach ($date_tabel as $rand): ?>
                                            <tr>
                                                <?php if ($rol === 'admin'): ?>
                                                    <?php if ($pagina_curenta === 'studenti'): ?>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($rand['matricol']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['nume'] . ' ' . $rand['prenume']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['specializare']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['an_studiu']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['grupa']); ?></td>
                                                        <td><span class="badge bg-<?php echo $rand['finantare'] == 'buget' ? 'success' : 'danger'; ?>"><?php echo ucfirst(htmlspecialchars($rand['finantare'])); ?></span></td>
                                                        <td class="text-center d-print-none">
                                                            <button class="btn btn-sm btn-warning" onclick="editeazaStudent(<?php echo htmlspecialchars(json_encode($rand)); ?>)"><i class="fas fa-edit"></i></button>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="action" value="sterge_student">
                                                                <input type="hidden" name="id_utilizator" value="<?php echo $rand['id_student']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                            </form>
                                                        </td>
                                                    <?php elseif ($pagina_curenta === 'profesori'): ?>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($rand['nume'] . ' ' . $rand['prenume']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['departament']); ?></td>
                                                    <?php elseif ($pagina_curenta === 'discipline'): ?>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($rand['denumire']); ?></td>
                                                        <td>Anul <?php echo htmlspecialchars($rand['an_studiu']); ?></td>
                                                        <td>Semestrul <?php echo htmlspecialchars($rand['semestru']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['credite']); ?> pct.</td>
                                                    <?php endif; ?>
                                                
                                                <?php elseif ($rol === 'profesor'): ?>
                                                    <?php if ($pagina_curenta === 'materiile_mele'): ?>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($rand['denumire']); ?></td>
                                                        <td>Anul <?php echo htmlspecialchars($rand['an_studiu']); ?></td>
                                                        <td>Semestrul <?php echo htmlspecialchars($rand['semestru']); ?></td>
                                                        <td class="text-center"><a href="dashboard.php?page=catalog&id_disciplina=<?php echo $rand['id_disciplina']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-list"></i> Vezi Catalog</a></td>
                                                    <?php elseif ($pagina_curenta === 'catalog'): ?>
                                                        <td><?php echo htmlspecialchars($rand['matricol']); ?></td>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($rand['nume'] . ' ' . $rand['prenume']); ?></td>
                                                        <td><?php echo htmlspecialchars($rand['grupa']); ?></td>
                                                        <td class="text-center">
                                                            <?php if ($rand['valoare']): ?>
                                                                <span class="badge bg-<?php echo $rand['valoare'] >= 5 ? 'success' : 'danger'; ?> fs-6"><?php echo htmlspecialchars($rand['valoare']); ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Neacordat</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center d-print-none">
                                                            <?php if (!$rand['valoare']): ?>
                                                                <form method="POST" class="d-flex justify-content-center align-items-center">
                                                                    <input type="hidden" name="action" value="adauga_nota">
                                                                    <input type="hidden" name="id_student" value="<?php echo $rand['id_student']; ?>">
                                                                    <input type="hidden" name="id_disciplina" value="<?php echo $_GET['id_disciplina']; ?>">
                                                                    <input type="number" name="valoare" min="1" max="10" class="form-control form-control-sm w-50 me-1" required>
                                                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                                                </form>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-warning" onclick="editeazaNota(<?php echo htmlspecialchars(json_encode($rand)); ?>)"><i class="fas fa-edit"></i></button>
                                                                <form method="POST" style="display:inline;">
                                                                    <input type="hidden" name="action" value="sterge_nota">
                                                                    <input type="hidden" name="id_nota" value="<?php echo $rand['id_nota']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                
                                                <?php elseif ($rol === 'student'): ?>
                                                    <td class="fw-bold"><?php echo htmlspecialchars($rand['denumire']); ?></td>
                                                    <td><?php echo htmlspecialchars($rand['credite']); ?> pct.</td>
                                                    <td class="text-center">
                                                        <?php if ($rand['valoare']): ?>
                                                            <span class="badge bg-<?php echo $rand['valoare'] >= 5 ? 'success' : 'danger'; ?> fs-6"><?php echo htmlspecialchars($rand['valoare']); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Fără notă</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($rand['data_notarii'] ?? '-'); ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="10" class="text-center">Nu s-au găsit date.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($rol === 'admin' && $pagina_curenta === 'rapoarte'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Raport: Distribuția Notelor Acordate</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="dashboard.php" class="row g-3 mb-4 d-print-none align-items-end">
                            <input type="hidden" name="page" value="rapoarte">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Dată Început</label>
                                <input type="date" class="form-control" name="data_start" value="<?php echo htmlspecialchars($data_start); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Dată Sfârșit</label>
                                <input type="date" class="form-control" name="data_end" value="<?php echo htmlspecialchars($data_end); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sync-alt me-2"></i>Generează Raport</button>
                            </div>
                        </form>
                        
                        <div class="alert alert-info">
                            <strong>Perioada selectată:</strong> <?php echo htmlspecialchars($data_start); ?> / <?php echo htmlspecialchars($data_end); ?><br>
                            S-au acordat în total <strong><?php echo array_sum($statistici_note); ?></strong> note.
                        </div>

                        <div style="max-height: 350px; display: flex; justify-content: center;">
                            <canvas id="graficNote"></canvas>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($rol === 'admin' && $pagina_curenta === 'istoric'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Istoric Activitate (Audit Log)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Data / Ora</th>
                                        <th>Utilizator</th>
                                        <th>Acțiune</th>
                                        <th>Detalii</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($istoric_date as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['data_ora']); ?></td>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($log['username'] ?? 'Sistem'); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($log['actiune']); ?></span></td>
                                        <td><?php echo htmlspecialchars($log['detalii']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <?php if ($rol === 'admin' && $pagina_curenta === 'studenti'): ?>
    <div class="modal fade" id="adaugaStudentModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="dashboard.php?page=studenti">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Adaugă Student</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="adauga_student">
                    <div class="mb-3"><label>Nr. Matricol</label><input type="text" class="form-control" name="matricol" required></div>
                    <div class="row"><div class="col-6 mb-3"><label>Nume</label><input type="text" class="form-control" name="nume" required></div><div class="col-6 mb-3"><label>Prenume</label><input type="text" class="form-control" name="prenume" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Spec.</label><select class="form-select" name="specializare"><option value="CTI">CTI</option><option value="AIA">AIA</option><option value="ETC">ETC</option></select></div><div class="col-6 mb-3"><label>An</label><input type="number" class="form-control" name="an_studiu" min="1" max="4" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Grupa</label><input type="text" class="form-control" name="grupa" required></div><div class="col-6 mb-3"><label>Finanțare</label><select class="form-select" name="finantare"><option value="buget">Buget</option><option value="taxa">Taxă</option></select></div></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Salvează</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="editeazaStudentModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="dashboard.php?page=studenti">
                <div class="modal-header bg-warning"><h5 class="modal-title text-dark">Modifică Date Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifica_student">
                    <input type="hidden" name="id_student" id="edit_id">
                    <div class="mb-3"><label>Nr. Matricol</label><input type="text" class="form-control" id="edit_matricol" readonly></div>
                    <div class="row"><div class="col-6 mb-3"><label>Nume</label><input type="text" class="form-control" name="nume" id="edit_nume" required></div><div class="col-6 mb-3"><label>Prenume</label><input type="text" class="form-control" name="prenume" id="edit_prenume" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Spec.</label><select class="form-select" name="specializare" id="edit_specializare"><option value="CTI">CTI</option><option value="AIA">AIA</option><option value="ETC">ETC</option></select></div><div class="col-6 mb-3"><label>An</label><input type="number" class="form-control" name="an_studiu" id="edit_an" required></div></div>
                    <div class="row"><div class="col-6 mb-3"><label>Grupa</label><input type="text" class="form-control" name="grupa" id="edit_grupa" required></div><div class="col-6 mb-3"><label>Finanțare</label><select class="form-select" name="finantare" id="edit_finantare"><option value="buget">Buget</option><option value="taxa">Taxă</option></select></div></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-warning text-dark fw-bold">Actualizează</button></div>
            </form>
        </div></div>
    </div>
    
    <script>
        function editeazaStudent(date) {
            document.getElementById('edit_id').value = date.id_student;
            document.getElementById('edit_matricol').value = date.matricol;
            document.getElementById('edit_nume').value = date.nume;
            document.getElementById('edit_prenume').value = date.prenume;
            document.getElementById('edit_specializare').value = date.specializare;
            document.getElementById('edit_an').value = date.an_studiu;
            document.getElementById('edit_grupa').value = date.grupa;
            document.getElementById('edit_finantare').value = date.finantare;
            new bootstrap.Modal(document.getElementById('editeazaStudentModal')).show();
        }
    </script>
    <?php endif; ?>

    <?php if ($rol === 'profesor' && $pagina_curenta === 'catalog'): ?>
    <div class="modal fade" id="editeazaNotaModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning"><h5 class="modal-title text-dark">Corectare Notă</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifica_nota">
                    <input type="hidden" name="id_nota" id="edit_id_nota">
                    <div class="mb-3"><label>Student</label><input type="text" class="form-control" id="edit_nume_student" readonly></div>
                    <div class="mb-3"><label>Noua Notă (1-10)</label><input type="number" class="form-control" name="valoare" id="edit_valoare" min="1" max="10" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-warning text-dark fw-bold">Actualizează Nota</button></div>
            </form>
        </div></div>
    </div>
    <script>
        function editeazaNota(date) {
            document.getElementById('edit_id_nota').value = date.id_nota;
            document.getElementById('edit_nume_student').value = date.nume + ' ' + date.prenume;
            document.getElementById('edit_valoare').value = date.valoare;
            new bootstrap.Modal(document.getElementById('editeazaNotaModal')).show();
        }
    </script>
    <?php endif; ?>

    <?php if ($rol === 'admin' && $pagina_curenta === 'rapoarte'): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('graficNote');
            if(ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Restanțe (Sub 5)', 'Note 5-6', 'Note 7-8', 'Note 9-10'],
                        datasets: [{
                            label: 'Număr de note acordate',
                            data: <?php echo json_encode(array_values($statistici_note)); ?>,
                            backgroundColor: ['rgba(220, 53, 69, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(13, 110, 253, 0.7)', 'rgba(25, 135, 84, 0.7)'],
                            borderColor: ['rgba(220, 53, 69, 1)', 'rgba(255, 193, 7, 1)', 'rgba(13, 110, 253, 1)', 'rgba(25, 135, 84, 1)'],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });
            }
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>