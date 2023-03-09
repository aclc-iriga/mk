<?php

    require_once '../config/database.php';
    
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.png">

      <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="dist/bootstrap-4.2.1/css/bootstrap.min.css">

    <!-- For Icon -->
    <link rel="stylesheet" href="https://kit.fontawesome.com/3142f33457.css" crossorigin="anonymous">

    <title>CRUD</title>

  </head>
  <body>
    
     <!-- Modal -->
     <!-- ADD POP UP FORM (Bootstrap MODAL) -->
     <div class="modal fade" id="addmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <?php
                            require_once '../models/Category.php';

                            $page_url = $_SERVER['REQUEST_URI'];
                            $entity_category = '';
                            $categories = Category::all();
                            foreach ($categories as $category) {
                                $category_url = strtolower(str_replace(' ', '-', $category->getSlug()));
                                if (strpos($page_url, $category_url) !== false) {
                                    $entity_category = $category->getTitle();
                                    break;
                                }
                            }
                        ?>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required <?php if (!empty($entity_category)) echo 'disabled'; ?>>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category) {
                                    $selected = ($category->getTitle() == $entity_category) ? 'selected' : '';
                                    echo "<option value={$category->getId()} $selected>{$category->getTitle()}</option>";
                                } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="Enter your Slug" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter your Title" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" name="insertdata" class="btn btn-primary">Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT POP UP FORM (Bootstrap MODAL) -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="update_id" id="update_id">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php
                                require_once '../models/Category.php';

                                $categories = Category::all();

                                foreach ($categories as $category) {
                                    echo "<option value={$category->getId()}>{$category->getTitle()}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" placeholder="Enter your Slug">
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Enter your Title">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" name="updatedata" class="btn btn-primary">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE POP UP FORM (Bootstrap MODAL) -->
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <h4>Do you want to Delete this Data ??</h4>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal"> NO </button>
                        <button type="submit" name="deletedata" class="btn btn-danger"> Yes !! Delete it. </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container my-3">
        <div class="card">
            <div class="card-body">
                <h1 class="text-center"><b> <u>Events</u> </b></h1>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary mr-3 my-3" data-toggle="modal" data-target="#addmodal">ADD DATA</button>
                    <div class="btn-group" role="group" aria-label="Go to">
                        <select onchange="window.location.href=this.value" class="btn btn-secondary">
                            <option selected value="">Go to...</option>
                            <option value="competitions.php">Competitions</option>
                            <option value="categories.php">Categories</option>
                            <option value="criteria.php">Criterion</option>
                            <option value="teams.php">Teams</option>
                            <option value="judges.php">Judges</option>
                            <option value="technicals.php">Technicals</option>
                        </select>
                    </div>
                    <div class="btn-group ml-auto" role="group" aria-label="Go to">
                        <?php require_once '../models/Category.php'; ?>
                        <select onchange="window.location = `${window.location.pathname}${this.value !== '' ? '?category=' + this.value : ''}`" class="btn btn-dark">
                            <option selected value="">All Categories</option>
                            <?php foreach(Category::rows() as $category) { ?>
                                <option value="<?= $category['slug'] ?>"
                                    <?php
                                    if(isset($_GET['category'])) {
                                        if(strtolower(trim($_GET['category'])) == $category['slug'])
                                            echo " selected";
                                    }
                                    ?>
                                ><?= $category['title'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php
                    require_once '../config/database.php';
                    require_once '../models/Event.php';
                    require_once '../models/Category.php';

                    $events = [];
                    if(isset($_GET['category'])) {
                        $category = Category::findBySlug($_GET['category']);
                        if($category)
                            $events = $category->getAllEvents();
                        else
                            $events = Event::all();
                    }
                    else
                        $events = Event::all();
                ?>
                <table id="datatableid" class="table table-bordered table-info table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="d-none">ID</th>
                            <th scope="col" class="d-none">Categories_ID</th>
                            <th scope="col">Slug</th>
                            <th scope="col">Title</th>
                            <th scope="col">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event) { ?>
                            <tr>
                                <td class="d-none"><?php echo $event->getId(); ?></td>
                                <td class="d-none"><?php echo $event->getCategoryId(); ?></td>
                                <td><?php echo $event->getSlug(); ?></td>
                                <td><?php echo $event->getTitle(); ?></td>
                                <td>
                                    <button type="button" class="btn btn-success editbtn"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button type="button" class="btn btn-danger deletebtn"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>                    
            </div>
        </div>
    </div>

    <!-- Bootstrap Javascript -->
    <script src="dist/ajax/libs/jquery-3.3.1/jquery.min.js"></script>
    <script src="dist/bootstrap-4.2.1/js/bootstrap.min.js"></script>

    <!-- For Icon -->
    <script src="dist/fontawesome/icon.js"></script>

    <script src="main.js"></script>

    <script>
        $(document).ready(function () {

            $('.editbtn').on('click', function () {

                $('#editmodal').modal('show');

                $tr = $(this).closest('tr');

                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();

                console.log(data);

                $('#update_id').val(data[0]);
                $('#category_id').val(data[1]);
                $('#slug').val(data[2]);
                $('#title').val(data[3]);
            });
        });
    </script>

</body>
</html>
