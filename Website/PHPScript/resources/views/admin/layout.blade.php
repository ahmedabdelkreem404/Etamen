<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8" />
      <title>@yield('title')</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta content='{{__("message.System Name")}}' name="description" />
      <meta content='{{__("message.System Name")}}' name="author" />
      <meta property="og:type" content="website" />
      <meta property="og:url" content="{{url('/')}}" />
      <meta property="og:title" content='{{__("message.System Name")}}' />
      <meta property="og:image" content="{{Session::get('favicon')}}" />
      <meta property="og:image:width" content="250px" />
      <meta property="og:image:height" content="250px" />
      <meta property="og:site_name" content='{{__("message.System Name")}}' />
      <meta property="og:description" content='{{__("message.meta_description")}}' />
      <meta property="og:keyword" content='{{__("message.Meta Keyword")}}' />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/libs/dropzone/min/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
      <link rel="shortcut icon" href="{{Session::get('favicon')}}" />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
      <link rel="stylesheet" href="{{asset('public/admin_design/layouts/vertical/assets/libs/twitter-bootstrap-wizard/prettify.css')}}" />
      @if(__("message.RTL")==0)
      <link href="{{asset('public/admin_design/layouts/vertical/assets/css/app-rtl.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />
      @else
      <link href="{{asset('public/admin_design/layouts/vertical/assets/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />
      @endif
      <link href="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
      <link href="{{asset('public/admin_design/layouts/vertical/assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css" />
      <script type="text/javascript" src='https://maps.google.com/maps/api/js?key={{Config::get("mapdetail.key")}}&sensor=false&libraries=places'></script>
   </head>
   <body>
      <div id="layout-wrapper">
         <header id="page-topbar">
            <div class="navbar-header">
               <div class="d-flex">
                  <div class="navbar-brand-box">
                     <a href="{{url('admin/dashboard')}}" class="logo logo-dark">
                     <span class="logo-sm">
                     <img src="{{Session::get('logo')}}" alt="" height="22" />
                     </span>
                     <span class="logo-lg">
                     <img src="{{Session::get('logo')}}" alt="" height="20" />
                     </span>
                     </a>
                     <a href="{{url('admin/dashboard')}}" class="logo logo-light">
                     <span class="logo-sm">
                     <img src="{{Session::get('logo')}}" alt="" height="22" />
                     </span>
                     <span class="logo-lg">
                     <img src="{{Session::get('logo')}}" alt="" height="20" />
                     </span>
                     </a>
                  </div>
                  <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
                  <i class="fa fa-fw fa-bars"></i>
                  </button>
                 
               </div>
               <div class="d-flex">
                  <div class="dropdown d-none d-lg-inline-block ml-1">
                     <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                     <i class="uil-minus-path"></i>
                     </button>
                  </div>
                  <div class="dropdown d-inline-block">
                     <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown" data-toggle="dropdown" id="bell-button" aria-haspopup="true" aria-expanded="false">
                     <i class="uil-bell"></i>
                     <span class="badge badge-danger badge-pill" id="ordercount">0</span>
                     </button>
                     <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="page-header-notifications-dropdown" id="notificationshow">
                        <div class="p-3">
                           <div class="row align-items-center">
                              <div class="col">
                                 <p class="red" id="notificationmsg"></p>
                              </div>
                           </div>
                        </div>
                        <div data-simplebar style="max-height: 230px;"></div>
                        <div class="p-2 border-top"></div>
                     </div>
                  </div>
                  <div class="dropdown d-inline-block">
                     <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <img class="rounded-circle header-profile-user" src="{{asset('public/upload/profile/profile.png')}}"
                     <span class="d-none d-xl-inline-block ml-1 font-weight-medium font-size-15">{{Session::get("username")}}</span>
                     <i class="uil-angle-down d-none d-xl-inline-block font-size-15"></i>
                     </button>
                     <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{url('admin/editprofile')}}"><i class="uil uil-user-circle font-size-18 align-middle text-muted mr-1"></i> <span class="align-middle">{{__("message.View Profile")}}</span></a>
                        <a class="dropdown-item" href="{{url('admin/changepassword')}}"><i class="mdi mdi-key font-size-18 align-middle text-muted mr-1"></i> <span class="align-middle">{{__("message.Change Password")}}</span></a>
                        <a class="dropdown-item" href="{{url('admin/setting')}}"><i class="uil uil-sliders-v-alt font-size-18 align-middle text-muted mr-1"></i> <span class="align-middle">{{__("message.Setting")}}</span></a>
                        <a class="dropdown-item" href="{{url('admin/logout')}}"><i class="uil uil-sign-out-alt font-size-18 align-middle mr-1 text-muted"></i> <span class="align-middle">{{__("message.Sign out")}}</span></a>
                     </div>
                  </div>
                  <div class="dropdown d-inline-block"></div>
               </div>
            </div>
         </header>
         <div class="vertical-menu">
            <div class="navbar-brand-box">
               <a href="{{url('admin/dashboard')}}" class="logo logo-dark">
               <span class="logo-sm">
               <img src="{{Session::get('logo')}}" alt="" height="22" />
               </span>
               <span class="logo-lg">
               <img src="{{Session::get('logo')}}" alt="" height="45" />
               </span>
               </a>
               <a href="{{url('admin/dashboard')}}" class="logo logo-light">
               <span class="logo-sm">
               <img src="{{Session::get('logo')}}" alt="" height="22" />
               </span>
               <span class="logo-lg">
               <img src="{{Session::get('logo')}}" alt="" height="20" />
               </span>
               </a>
            </div>
            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
            <i class="fa fa-fw fa-bars"></i>
            </button>
            <div data-simplebar class="sidebar-menu-scroll">
               <div id="sidebar-menu">
                  <ul class="metismenu list-unstyled" id="side-menu">
                     <li class="menu-title">{{__("message.Menu")}}</li>
                     <li>
                        <a href="{{url('admin/dashboard')}}">
                        <i class="uil-home-alt"></i><span class="badge badge-pill badge-primary float-right"></span>
                        <span>{{__("message.Dashboard")}}</span>
                        </a>
                     </li>
                     <li class="menu-title">{{__("message.Appointment")}}</li>
                     <li>
                        <a href="{{url('admin/appointment')}}" class="waves-effect">
                        <i class="uil-shutter-alt"></i>
                        <span>{{__("message.Appointment")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/doctors')}}" class="waves-effect">
                        <i class="uil-flask"></i>
                        <span>{{__("message.Doctors")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/patients')}}" class="waves-effect">
                        <i class="uil-file-alt"></i>
                        <span>{{__('message.Patients')}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/services')}}" class="waves-effect">
                        <i class="uil-adjust-alt"></i>
                        <span>{{__("message.Department")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/banner')}}" class="waves-effect">
                        <i class="uil-image"></i>
                        <span>{{__("message.banner")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/reviews')}}" class="waves-effect">
                        <i class="uil-star"></i>
                        <span>{{__("message.Review")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/complain')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.complain")}}</span>
                        </a>
                     </li>
                      <?php if(env('IS_FORNT')=="1")
                     {
                        ?>
                      <li>
                        <a href="{{url('admin/contact_list')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Contact")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/news')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.News")}}</span>
                        </a>
                     </li>
                      <?php
                     }?>
                     
                     <li>
                        <a href="{{url('admin/medicines')}}">
                            <i class="fas fa-pills"></i>
                            <span>{{__("Medicines")}}</span>
                        </a>
                    </li>
                     
                     <li class="menu-title">{{__("message.Privecy")}}</li>
                     <li>
                        <a href="{{url('admin/about')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.About")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/Terms_condition')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.term")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/app_privacy')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Privecy")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/data_deletion')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Data-Deletion")}}</span>
                        </a>
                     </li>
                     
                     <li class="menu-title">Reports</li>
                     <li>
                        <a href="{{route('doctor_report')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>Doctors </span>
                        </a>
                     </li>
                     <li>
                        <a href="{{route('user_report')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>User </span>
                        </a>
                     </li>
                     <li>
                        <a href="{{route('do_sub_report')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>Doctor Subscription </span>
                        </a>
                     </li>
                     <li>
                        <a href="{{route('app_book_report')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>Appointment booked </span>
                        </a>
                     </li>
                     
                     <li class="menu-title">{{__("message.Payment")}}</li>
                     <li>
                        <a href="{{url('admin/pending_payment')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Pending Payment")}}</span>
                        </a>
                     </li>
                      <?php if(env('IS_FORNT')=="1")
                     {
                        ?>
                       <li>
                        <a href="{{route('Subscription')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Subscription")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/subscriber_doc')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Subscriber")}}</span>
                        </a>
                     </li>
                      <?php
                     }?>
                      <li>
                        <a href="{{url('admin/complete_payment')}}" class="waves-effect">
                        <i class="uil-invoice"></i>
                        <span>{{__("message.Complete Payment")}}</span>
                        </a>
                     </li>
                     <li class="menu-title">{{__("message.Notification")}}</li>
                     <li>
                        <a href="{{url('admin/sendnotification')}}" class="waves-effect">
                        <i class="uil-snapchat-ghost"></i>
                        <span>{{__("message.Send Notification")}}</span>
                        </a>
                     </li>
                     <li>
                        <a href="{{url('admin/notificationkey')}}" class="waves-effect">
                        <i class="uil-key-skeleton-alt"></i>
                        <span>{{__("message.Notification Key")}}</span>
                        </a>
                     </li>
                      <?php if(env('IS_FORNT')=="1")
                     {
                        ?>
                     <li>
                        <a href="{{route('payment-setting')}}" class="waves-effect">
                        <i class="uil-key-skeleton-alt"></i>
                        <span>{{__("message.Payment Gateway")}}</span>
                        </a>
                     </li>
                      <?php
                     }?>
                     
                  </ul>
               </div>
            </div>
         </div>
         @yield('content')
         <footer class="footer">
            <div class="container-fluid">
               <div class="row">
                  <div class="col-sm-6">
                     {{date('Y')}}
                     © {{__("message.System Name")}}
                  </div>
                  <div class="col-sm-6">
                     <div class="text-sm-right d-none d-sm-block">
                        <i class="mdi mdi-heart text-danger"></i> {{__("message.by")}} <a href="https://themesbrand.com/" target="_blank" class="text-reset">{{__("message.System Name")}}</a>
                     </div>
                  </div>
               </div>
            </div>
         </footer>
      </div>
   </div>
      <div class="rightbar-overlay"></div>
      <input type="hidden" id="siteurl" value="{{url('admin')}}" />
      <input type="hidden" id="delete_record" value="{{__('message.delete_record')}}">
      <input type="hidden" id="today_no_appointment_msg" value='{{__("message.You dont have any  appointments for today")}}'/>
      <input type="hidden" id="demo" value="{{Session::get('is_demo')}}"/>
      <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
      <input type="hidden" id="soundnotify" value="{{asset('public/sound/notification/notification.mp3')}}" />
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/jquery/jquery.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/metismenu/metisMenu.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/simplebar/simplebar.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/node-waves/waves.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/waypoints/lib/jquery.waypoints.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/jquery.counterup/jquery.counterup.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/dropzone/min/dropzone.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/js/app.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
      <script src="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/jszip/jszip.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/pdfmake/build/pdfmake.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/pdfmake/build/vfs_fonts.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/js/pages/datatables.init.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/select2/js/select2.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/js/pages/ecommerce-add-product.init.js')}}"></script>
      <script src="{{url('public/js/locationpicker.js')}}"></script>
      <script type="text/javascript" src="{{asset('public/js/admin.js?v=rgtrygr')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/libs/twitter-bootstrap-wizard/prettify.js')}}"></script>
      <script src="{{asset('public/admin_design/layouts/vertical/assets/js/pages/form-wizard.init.js')}}"></script>
      <script>
          function disablebtn(){
                alert("This Action Disable In Demo");
            }
      </script>
      @yield('footer')
   </body>
</html>