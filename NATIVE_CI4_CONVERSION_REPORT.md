# Native CI4 pattern conversion report

I converted common CI3 patterns to native CI4 patterns across Models, Views, Controllers, and Helpers.

Changed PHP files: 198
Files still containing old/complex patterns: 71
PHP lint errors in changed sample: 2

Important limitation:
A full CI3-to-CI4 migration cannot be guaranteed lossless by regex/mechanical conversion because many old Query Builder calls depend on shared builder state. I preserved business logic where possible and converted clear/simple patterns, but complex chains must be tested route-by-route.

Remaining old/complex patterns:
- system/Session/SessionInterface.php: CI3 session userdata
- app/Controllers/Admin.php: direct db where, direct db select, direct db from, this load
- app/Controllers/Api.php: direct db where, this load
- app/Controllers/Api_files.php: direct db where, this load
- app/Controllers/Api_instructor.php: this load
- app/Controllers/Blog.php: direct db where, this load
- app/Controllers/Checker.php: CI3 get_where, direct db where, direct db from, this load
- app/Controllers/Courseapi.php: direct db select, direct db from, this load
- app/Controllers/Data_center.php: direct db where, this load
- app/Controllers/Files.php: this load
- app/Controllers/Home.php: get_instance, direct db where, direct db select, direct db from, this load
- app/Controllers/Install.php: direct db where, this load
- app/Controllers/Login.php: direct db where, this load
- app/Controllers/Modal.php: CI3 session userdata, this load
- app/Controllers/Page.php: direct db where, this load
- app/Controllers/Payment.php: direct db where, this load
- app/Controllers/Sign_up.php: this load
- app/Controllers/Sitemap.php: this load
- app/Controllers/Sso.php: CI3 get_where, this load
- app/Controllers/Updater.php: this load
- app/Controllers/User.php: CI3 get_where, direct db where, direct db from, this load
- app/Controllers/View.php: this load
- app/Helpers/addon_helper.php: get_instance, CI3 get_where
- app/Helpers/ci3_compat_helper.php: get_instance
- app/Helpers/multi_language_helper.php: get_instance, CI3 session userdata
- app/Helpers/user_helper.php: get_instance, CI3 get_where
- app/Libraries/Format.php: get_instance
- app/Libraries/REST_Controller.php: this load
- app/Models/Academy_cloud_model.php: direct db where
- app/Models/Addon_model.php: direct db where
- app/Models/Api_instructor_model.php: CI3 get_where, direct db where, direct db select
- app/Models/Api_model.php: CI3 get_where, direct db where, this load
- app/Models/Crud_model.php: get_instance, CI3 get_where, direct db where, direct db select, direct db from, this load
- app/Models/Email_model.php: direct db where, this load
- app/Models/Lazyloaddata_model.php: direct db where
- app/Models/Payment_model.php: direct db where
- app/Models/User_model.php: CI3 get_where, direct db where
- app/Views/lessons/quiz_view.php: CI3 get_where
- app/Views/mobile/quiz_view.php: CI3 get_where
- app/Views/frontend/default-new/home_1.php: CI3 get_where
- app/Views/frontend/default-new/home_2.php: CI3 get_where
- app/Views/frontend/default-new/home_3.php: CI3 get_where
- app/Views/frontend/default-new/home_4.php: CI3 get_where
- app/Views/frontend/default-new/home_5.php: CI3 get_where
- app/Views/frontend/default-new/home_6.php: CI3 get_where
- app/Views/frontend/default-new/home_7.php: CI3 get_where
- app/Views/frontend/default-new/home_cooking2.php: CI3 get_where
- app/Views/frontend/default-new/home_elegant.php: CI3 get_where
- app/Views/frontend/default-new/home_fitness.php: CI3 get_where
- app/Views/components/main/courses_fitness.php: CI3 get_where
- app/Views/components/main/topbar.php: get_instance, CI3 session userdata
- app/Views/components/main/upcoming_courses.php: CI3 get_where
- app/Views/components/main/upcoming_courses_2.php: CI3 get_where
- app/Views/components/main/upcoming_courses_5.php: CI3 get_where
- app/Views/components/main/upcoming_courses_6.php: CI3 get_where
- app/Views/backend/admin/student_academic_quiz_result.php: CI3 get_where
- app/Views/backend/user/student_academic_quiz_result.php: CI3 get_where
- app/Models/addons/Ai_model.php: direct db where, this load
- app/Models/addons/Assignment_model.php: direct db where
- app/Models/addons/Certificate_model.php: direct db where
- app/Models/addons/Customer_support_model.php: direct db where
- app/Models/addons/Ebook_model.php: direct db where, direct db select
- app/Models/addons/Team_package_model.php: CI3 get_where, direct db where, direct db select, direct db from
- app/Controllers/addons/Assignment.php: direct db where, this load
- app/Controllers/addons/Certificate.php: direct db where, this load
- app/Controllers/addons/Customer_support.php: this load
- app/Controllers/addons/Ebook.php: direct db where, this load
- app/Controllers/addons/Ebook_manager.php: this load
- app/Controllers/addons/Team_training.php: direct db where, direct db select, this load
- app/Controllers/Hrchr/Enrollments.php: this load
- app/Controllers/Hrchr/Progress.php: this load

PHP lint errors:
## app/Controllers/Checker.php
Errors parsing /mnt/data/lms_hrchr_native_ci4_work/app/Controllers/Checker.php
PHP Parse error:  syntax error, unexpected token "\" in /mnt/data/lms_hrchr_native_ci4_work/app/Controllers/Checker.php on line 725

## app/Models/addons/Ebook_model.php
Errors parsing /mnt/data/lms_hrchr_native_ci4_work/app/Models/addons/Ebook_model.php
PHP Parse error:  syntax error, unexpected token "\" in /mnt/data/lms_hrchr_native_ci4_work/app/Models/addons/Ebook_model.php on line 15
