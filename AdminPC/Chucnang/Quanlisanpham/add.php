<?php
include('../../assets/database/connect.php');

// Khởi tạo biến thông báo
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id = trim(mysqli_real_escape_string($conn, $_POST['id']));
    $title = trim(mysqli_real_escape_string($conn, $_POST["title"]));
    $amount = trim(mysqli_real_escape_string($conn, $_POST['amount']));
    $old_price = trim(mysqli_real_escape_string($conn, $_POST["old_price"]));
    $new_price = trim(mysqli_real_escape_string($conn, $_POST["new_price"]));
    $featured = trim(mysqli_real_escape_string($conn, $_POST['featured']));
    $description = trim(mysqli_real_escape_string($conn, $_POST["description"]));
    $iddanhmuc = trim(mysqli_real_escape_string($conn, $_POST["categoryid"]));
    
    // Kiểm tra dữ liệu bắt buộc
    if ($id === '' || $title === '' || $amount === '' || $old_price === '' || $new_price === '' || $featured === '' || $description === '' || $iddanhmuc === '') {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } else {
        // Kiểm tra ID đã tồn tại chưa
        $check_id = mysqli_query($conn, "SELECT ID FROM product_info WHERE ID = '$id'");
        if(mysqli_num_rows($check_id) > 0) {
            $error = "ID đã tồn tại, vui lòng chọn ID khác!";
        } else {
            // Xử lý upload ảnh chính
            $filename = '';
            if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = basename($_FILES["image"]["name"]);
                $filetype = $_FILES["image"]["type"];
                $filesize = $_FILES["image"]["size"];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if(!array_key_exists($ext, $allowed)) {
                    $error = "Lỗi: Vui lòng chọn định dạng file hợp lệ.";
                } elseif($filesize > 5 * 1024 * 1024) {
                    $error = "Lỗi: Kích thước file quá lớn.";
                } elseif(!in_array($filetype, $allowed)) {
                    $error = "Lỗi: Định dạng file không hợp lệ.";
                } else {
                    if (!file_exists("../../assets/image/")) {
                        mkdir("../../assets/image/", 0777, true);
                    }
                    $upload_path = "../../assets/image/" . $filename;
                    if(!move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)) {
                        $error = "Lỗi: Không thể upload file.";
                    }
                }
            }
            // Nếu không có lỗi, thực hiện thêm sản phẩm
            if(empty($error)) {
                $sql = "INSERT INTO product_info (ID, title, amount, old_price, new_price, featured, description, image_name, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssssss", $id, $title, $amount, $old_price, $new_price, $featured, $description, $filename, $iddanhmuc);
                if(mysqli_stmt_execute($stmt)) {
                    // Upload các ảnh mô tả nếu có
                    if(isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                        $files = $_FILES['images'];
                        $file_names = $files['name'];
                        foreach ($file_names as $key => $value) {
                            if($files['error'][$key] == 0) {
                                $desc_upload_path = "../../assets/image/" . basename($value);
                                move_uploaded_file($files['tmp_name'][$key], $desc_upload_path);
                                // Có thể thêm code để lưu thông tin ảnh mô tả vào database nếu cần
                            }
                        }
                    }
                    // Chỉ chuyển hướng khi chưa gửi header hoặc echo gì ra trước đó
                    header('Location: ../quanlisanpham.php');
                    exit();
                } else {
                    $error = "Lỗi: Không thể thêm sản phẩm.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DASHBOARD ADMIN</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta content="" name="keywords" />
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon" />
    <link rel="icon" href="../../assets/image/admin.png">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet" />
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <!-- Customized Bootstrap Stylesheet -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Template Stylesheet -->
    <link href="../../assets/css/style.css" rel="stylesheet" />
    <script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="../../trangquantri.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">
                        <i class="fa fa-hashtag me-2"></i>ELECTRO
                    </h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="../../assets/image/avatar.jpg" alt=""
                            style="width: 40px; height: 40px" />
                        <div
                            class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">Bien</h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="../../trangquantri.php" class="nav-item nav-link "><i
                            class="fa fa-tachometer-alt me-2"></i>Trang chủ</a>
                    <a href="../quanlithongtinnguoidung.php" class="nav-item nav-link"><i
                            class="fa fa-tachometer-alt me-2"></i>Quản lý thông tin người
                        dùng</a>
                    <a href="../quanlidanhmuc.php" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Quản
                        lý danh mục</a>
                    <a href="../quanlisanpham.php" class="nav-item nav-link active"><i
                            class="fa fa-keyboard me-2"></i>Quản lý
                        sản
                        phẩm</a>
                    <a href="../quanlisanphamnoibat.php" class="nav-item nav-link"><i class="fa fa-table me-2"></i>Quản
                        lý sản phẩm nổi
                        bật</a>
                    <a href="../quanlidonhang.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Quản lý
                        đơn
                        hàng</a>
                    <a href="../quanlithongtindonhang.php" class="nav-item nav-link"><i
                            class="fa fa-chart-bar me-2"></i>Quản lý thông tin
                        đơn hàng</a>
                    <a href="../quanlibinhluan.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Quản
                        lý
                        bình
                        luận</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="../../assets/image/avatar.jpg" alt=""
                                style="width: 40px; height: 40px" />
                            <span class="d-none d-lg-inline-flex">Quản lý</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../../logout.php" class="dropdown-item">Đăng xuất</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- ==================================================== Start Main Content ========================================================== -->
            <div class="mt-5 mx-5">
                <div class="text-center">
                    <h3>Thêm sản phẩm</h3>
                </div>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <form action="" method="POST" role="form" enctype="multipart/form-data">
                        <?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if(!empty($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
                        <div class="form-group">
                            <label for="">ID</label>
                            <input type="text" name="id" placeholder="ID" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" name="title" placeholder="Title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">Quantity</label>
                            <input type="text" name="amount" placeholder="Số lượng" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">Old Price</label>
                            <input type="text" name="old_price" placeholder="Old price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">New Price</label>
                            <input type="text" name="new_price" placeholder="New price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">Featured</label>
                            <select name="featured" aria-labelledby="state">
                                <option value="<?php echo "Không" ?>">Không</option>
                                <option value="<?php echo "Có" ?>">Có</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Select image</label>
                            <input type="file" name="image" placeholder="Image">
                        </div>
                        <div class="form-group">
                            <label for="">Image description</label>
                            <input multiple="" type="file" name="images[]" placeholder="Image">
                        </div>
                        <div class="form-group">
                            <label for="">Category</label>
                            <select name="categoryid" aria-labelledby="state">
                                <?php
                                $sql = "SELECT * FROM danhmuc_info";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_array($result))
                                    echo '<option value="' . $row['id'] . '">' . $row['tendanhmuc'] . '</option>';
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Content</label>
                            <textarea name="description" id="product-content" class="form-control" required></textarea>
                        </div>
                        <br>
                        <div class="d-flex justify-content-center">
                            <button type="submit" name="submit" value="Add" class="btn btn-primary">Thêm</button>
                        </div>
                    </form>
                </table>
            </div>

            <!-- ==================================================== End Main Content ========================================================== -->

            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">ELECTRO</a>, All Right Reserved.
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            Designed by <a href="https://github.com/biennc/">Bien - Mai </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->
    </div>
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Template Javascript -->
    <script src="../../assets/js/main.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#product-content'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>