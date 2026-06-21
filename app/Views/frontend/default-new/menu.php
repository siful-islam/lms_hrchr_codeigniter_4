
 <ul class="navbar-nav main-nav-wrap me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a class="nav-link header-dropdown" href="#" id="navbarDropdown" >
            <i class="fas fa-bars pr-5"></i> <?php 
$crudModel = new \App\Models\Crud_model();
echo site_phrase('courses'); ?>
        </a>
        <ul class="navbarHover">
        <?php  $categories = $crudModel->get_categories()->getResultArray();
            foreach ($categories as $key => $category):?>
            <li  class="dropdown-submenu">
                <a  href="#">
                <span class="icons"><i class="<?php echo $category['font_awesome_class']; ?>"></i></span>
                <span class="text-cat"><?php echo $category['name']; ?></span>
                <span class="has-sub-category ms-auto"><i class="fa-solid fa-angle-right"></i></span>
                </a>
                <ul class="sub-category-menu">
                    <?php
                         $sub_categories = $crudModel->get_sub_categories($category['id']);
                         foreach ($sub_categories as $sub_category): ?>
                        <li><a href="#"> <?php echo $category['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
        </ul> 
    </li>
</ul>

