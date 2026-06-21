<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// CI3 migrated routes (phase 1)
$routes->get('/', 'Home::index');
$routes->get('/', 'Home::index');
$routes->add('certificate/(:segment)', 'Addons::Certificate/generate_certificate/$1');
$routes->add('course_bundles/(:segment)', 'Addons::Course_bundles/index/$1');
$routes->add('course_bundles', 'Addons::Course_bundles');
$routes->add('course_bundles/search/(:segment)', 'Addons::Course_bundles/search/$1');
$routes->add('course_bundles/search/(:segment)/(:segment)', 'Addons::Course_bundles/search/$1/$1');
$routes->add('bundle_details/(:segment)/(:segment)', 'Addons::Course_bundles/bundle_details/$1');
$routes->add('bundle_details/(:segment)', 'Addons::Course_bundles/bundle_details/$1/$1');
$routes->add('course_bundles/buy/(:segment)', 'Addons::Course_bundles/buy/$1');
$routes->add('home/my_bundles', 'Addons::Course_bundles/my_bundles');
$routes->add('home/bundle_invoice/(:segment)', 'Addons::Course_bundles/invoice/$1');
$routes->add('ebook/ebook_details/(:segment)/(:segment)', 'Addons::Ebook/ebook_details/$1/$2');
$routes->add('ebook', 'Addons::Ebook/ebooks');
$routes->add('ebook_manager/all_ebooks', 'Addons::Ebook_manager/all_ebooks');
$routes->add('ebook_manager/add_ebook', 'Addons::Ebook_manager/add_ebook');
$routes->add('ebook_manager/payment_history', 'Addons::Ebook_manager/payment_history');
$routes->add('ebook_manager/category', 'Addons::Ebook_manager/category');
$routes->add('ebook/buy/(:segment)', 'Addons::Ebook/buy/$1');
$routes->add('home/my_ebooks', 'Addons::Ebook/my_ebooks');
$routes->add('blogs', 'Blog::blogs');
$routes->add('blogs/(:segment)', 'Blog::blogs/$1');
$routes->add('page/(:segment)', 'Page::index/$1');
$routes->add('tutors', 'Addons::Tutor_booking/list_of_tuitions');
$routes->add('tutors/(:segment)', 'Addons::Tutor_booking/list_of_tuitions/$1');
$routes->add('tutor/filter', 'Addons::Tutor_booking/list_of_tuitions_after_filter');
$routes->add('schedules_bookings/(:segment)', 'Addons::Tutor_booking/tutor_details/$1');
$routes->add('my_bookings', 'Addons::Tutor_booking/booked_schedules_student');
$routes->add('sitemap.xml', 'Sitemap::index');


