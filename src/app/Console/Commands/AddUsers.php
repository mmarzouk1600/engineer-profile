<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\MuStudent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\Faculty\Entities\Faculty;
use Modules\Faculty\Entities\FacultySupervisor;

class AddUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faculties = HrDepartment::where('type', HrDepartment::COLLAGE_TYPE)->select('id','name','direct_manager_m_id','direct_manager_f_id')->get();

        foreach($faculties as $faculty) {

            $coordinator = HrEmployee::where('DEPT_CODE', $faculty->id)->first();

            $student = MuStudent::where(['erp_faculty'=> $faculty->id,'current_status'=>1])->first();
            $dean = HrEmployee::where('employee_id', $faculty->direct_manager_m_id)->orwhere('employee_id', $faculty->direct_manager_f_id)->first();


                $inserted = Faculty::updateOrCreate(
                    [
                        'faculty_id' => $faculty->id,
                    ],
                    [
                        'faculty_id' => $faculty->id,
                        'activated' => Faculty::ACTIVE_STATUS,
                    ]
                );


            if (   $dean) {
                $admin = Admin::updateOrCreate([
                    'email' => $dean->email,
                    'employee_id' => $dean->employee_id,
                    'national_id' => $dean->national_id,
                ], [
                    'name' => 'Dean ',
                    'email' => $dean->email ? $dean->email : time() . rand(11, 999) . "aaa@mu.sa",
                    'employee_id' => $dean->employee_id,
                    'national_id' => $dean->national_id,
                    'faculty_id' => $faculty->id,
                    'password' => Hash::make('asdasd'),
                    'type' => '2'
                ]);

                $admin->assignRole('dean');
                FacultySupervisor::updateOrCreate([
                    'faculty_id' => $inserted->id,
                    'admin_id' => $dean->id,
                    'role' => FacultySupervisor::DEAN_ROLE,
                ],
                    [
                        'faculty_id' => $inserted->id,
                        'admin_id' => $admin->id,
                        'role' => FacultySupervisor::DEAN_ROLE,
                    ]);

            }
                if ($coordinator){
                    $admin = Admin::updateOrCreate([
                        'email' => $coordinator->email,
                        'employee_id' => $coordinator->employee_id,
                        'national_id' => $coordinator->national_id,
                    ], [
                        'name' => 'Coordinator',
                        'email' => $coordinator->email? $coordinator->email:time().rand(11,999)."aaa@mu.sa",
                        'employee_id' => $coordinator->employee_id,
                        'national_id' => $coordinator->national_id,
                        'password' => Hash::make('asdasd'),
                        'type' => '2',
                        'faculty_id' => $faculty->id,
                    ]);

                    $admin->assignRole('coordinator');

                    FacultySupervisor::updateOrCreate([
                        'faculty_id' => $inserted->id,
                        'admin_id' => $coordinator->id,
                        'role' => FacultySupervisor::SUPERVISOR_ROLE,
                    ],
                        [
                            'faculty_id' => $inserted->id,
                            'admin_id' => $admin->id,
                            'role' => FacultySupervisor::SUPERVISOR_ROLE,
                        ]);

                }

                if ($student){
                    $admin = Admin::updateOrCreate([
                        'email' => $student->email,
                        'employee_id' => $student->student_id,
                        'national_id' => $student->national_id,
                    ], [
                        'name' => 'Student : '.  $student->full_name,
                        'email' => $student->email? $student->email:time().rand(11,999)."aaa@mu.sa",
                        'employee_id' => $student->student_id,
                        'national_id' => $student->national_id,
                        'password' => Hash::make('asdasd'),
                        'faculty_id' => $faculty->id,
                        'type' => '2'
                    ]);

                    $admin->assignRole('student');

                }



        }
        $roles = [
            'superAdmin',
            'admin',
        ];


       foreach($roles as $role){
                $admin = Admin::updateOrCreate([
                    'email' => $role.'@mu.edu.sa',
                    'employee_id' => $role.'11111111',
                    'national_id' => $role.'11111111',
                ],[
                    'name' => 'مدير ',
                    'email' => $role.'@mu.edu.sa',
                    'employee_id' => $role.'11111111',
                    'national_id' => $role.'11111111',
                    'password' => Hash::make('asdasd'),
                    'type' => $role == 'superAdmin' ? 1 : 2,
                ]);

                $admin->assignRole($role);

            }


    }
}
