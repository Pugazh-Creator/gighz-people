<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Days;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Tag\Select;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

use function PHPUnit\Framework\returnSelf;

class Hrms extends BaseController
{

    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }


    public function index()
    {
        return view('hrms/recruitment');
    }

    // ----------------------------------------------------------------------------------------------------
    // DOWNLOAD DOC

    public function downloadEmployeeForm($emp_id)
    {
        $originalFile = WRITEPATH . 'templates/hr/01_NEW JOINING FORM Employee.docx';

        // New filename with employee ID
        $newFileName = $emp_id . '_Employee_Joining_Form.docx';
        $newFilePath = WRITEPATH . 'temp/' . $newFileName;

        // Ensure temp folder exists
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0777, true);
        }

        // Copy template to new file
        copy($originalFile, $newFilePath);

        // Download renamed file
        return $this->response->download($newFilePath, null)->setFileName($newFileName);
    }

    public function downloadAppraisalquestion()
    {
        // Path to your file
        $originalFile = WRITEPATH . 'templates/hr/6-Appraisal Questions - GigHz.docx';

        if (!file_exists($originalFile)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        // Force download
        return $this->response->download($originalFile, null)->setFileName('Appraisal_Questionnaire.docx');
    }

    public function downloadExitClearenceForm()
    {
        // Path to your file
        $originalFile = WRITEPATH . 'templates/hr/7_EXIT CLEARANCE FORM updated.docx';

        if (!file_exists($originalFile)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        // Force download
        return $this->response->download($originalFile, null)->setFileName('Exit_Clearence_form.docx');
    }

    public function downloadExitInterviewForm()
    {
        // Path to your file
        $originalFile = WRITEPATH . 'templates/hr/8_EXIT INTERVIEW FORM updated.docx';

        if (!file_exists($originalFile)) {
            return $this->response->setStatusCode(404, 'File not found');
        }

        // Force download
        return $this->response->download($originalFile, null)->setFileName('Exit_Interview_Form.docx');
    }

    public function downloadUnpaiedInternForm($emp_id)
    {
        $originalFile = WRITEPATH . 'templates/hr/02_Students Internship Application Form.docx';

        // New filename with employee ID
        $newFileName = $emp_id . '_Intern_Joining_Form.docx';
        $newFilePath = WRITEPATH . 'temp/' . $newFileName;

        // Ensure temp folder exists
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0777, true);
        }

        // Copy template to new file
        copy($originalFile, $newFilePath);

        // Download renamed file
        return $this->response->download($newFilePath, null)->setFileName($newFileName);
    }


    // collecting new or existed data from ajax requirment page
    public function saveRequirement()
    {
        helper(['form', 'filesystem']);

        $db = db_connect();
        $request = $this->request;

        $is_new = $request->getPost('is_new'); // from form or frontend flag

        if ($is_new == 1) {
            // Collect new form data
            $firstname = $request->getPost('firstname');
            $lastname  = $request->getPost('lastname');
            $initial   = $request->getPost('initial');
            $dob   = $request->getPost('dob');
            $fullName  = trim($firstname . ' ' . $lastname . ' ' . $initial);
            $college = $request->getPost('college');
            $course = $request->getPost('course');
            $mail = $request->getPost('mail_id');
            $phone = $request->getPost('phone_no');
            $department = $request->getPost('department');
            $mode_of_apply = $request->getPost('mode_of_apply');
            $status = $request->getPost('status');
            $gender = $request->getPost('gender') ?? '';
            $exp = $request->getPost('experience') ?? '';
            $employeement_type = $request->getPost('employement_type') ?? '';

            // Handle file upload
            $resumeFile = $this->request->getFile('resume');
            if ($resumeFile && $resumeFile->isValid() && !$resumeFile->hasMoved()) {
                $newName = time() . '_' . $fullName . '_Resume';
                $resumeFile->move(FCPATH . 'uploads/resumes', $newName);
                $resumePath = 'uploads/resumes/' . $newName;
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Resume upload failed']);
            }

            $data = [
                'name' => trim($fullName),
                'first_name' => $firstname,
                'last_name' => $lastname,
                'initial' => $initial,
                'college' => $college,
                'course_name' => $course,
                'personal_mail' => $mail,
                'phone_no' => $phone,
                'resume' => $resumePath,
                'dob' => $dob,
                'department' => $department,
                'mode_of_apply' => $mode_of_apply,
                'requirment_status' => $status,
                'gender' => $gender,
                'experience' => $exp,
                'employment_type' => $employeement_type,
            ];

            $db->table('recruitment')->insert($data);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Recruitment saved.']);
        } else {
            // Update existing user status
            $id = $request->getPost('id');
            $status = $request->getPost('status');
            $scoreornote = $request->getPost('scoreornote');
            $dateofupdate = $request->getPost('dateofupdate');
            $scheduledType = $request->getPost('scheduledtype');
            $scheduledDate = $request->getPost('scheduleddate');
            $role = $request->getPost('std-role') ?? '';

            // return $this->response->setJSON(['data' => $role]);
            // return $this->response->setJSON(['data' => "$id - $status - $scoreornote - $dateofupdate"]);

            $exist_user = $db->query("SELECT * FROM recruitment WHERE recruitment_id = ? LIMIT 1", [$id])->getResultArray();
            if (empty($exist_user)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
            }


            $update = [];

            if ($status == 'aptitude') {
                $update = [
                    'aptitude_score' => $scoreornote,
                    'aptitude_date' => $dateofupdate,
                ];
            } elseif ($status == 'dept_question') {
                $update = [
                    'dept_question_score' => $scoreornote,
                    'dept_ques_date' => $dateofupdate,
                ];
            } elseif ($status == 'round_1') {
                $update = [
                    'round_1_note' => $scoreornote,
                    'round_1_date' => $dateofupdate,
                ];
            } elseif ($status == 'round_2') {
                $update = [
                    'round_2_note' => $scoreornote,
                    'round_2_date' => $dateofupdate,
                ];
            } elseif ($status == 'round_3') {
                $update = [
                    'round_3_note' => $scoreornote,
                    'round_3_date' => $dateofupdate,
                ];
            } elseif ($status == 'round_4') {
                $update = [
                    'round_4_note' => $scoreornote,
                    'round_4_date' => $dateofupdate,
                ];
            } elseif ($status == 'round_5') {
                $update = [
                    'round_5_note' => $scoreornote,
                    'round_5_date' => $dateofupdate,
                ];
            } elseif ($status == 'selected') {
                $result = $db->query("SELECT emp_id FROM employees ORDER BY no DESC LIMIT 1")->getRowArray();
                $input =  $result['emp_id'];
                preg_match('/^([A-Za-z]+)([0-9]+)$/', $input, $matches);
                $letters = $matches[1] ?? 'EMP';
                $number = $matches[2] ?? '0';
                $numberLength = strlen($number);
                $incremented = str_pad((int)$number + 1, $numberLength, '0', STR_PAD_LEFT);
                $newCode = $letters . $incremented;

                $dob = $exist_user[0]['dob'] ?? '2000-01-01';
                $birthDate = new DateTime($dob);
                $today = new DateTime('today');
                $age = $birthDate->diff($today)->y;

                if ($exist_user[0]['requirment_status'] !== 'selected') {
                    if ($exist_user[0]['employment_type'] == 'unpaid intern') {
                        // Use query builder manually
                        $builder = $db->table('interns');
                        $builder->select('intern_id');
                        $builder->orderBy('id', 'DESC');
                        $builder->limit(1);

                        $query = $builder->get();

                        if ($query->getNumRows() > 0) {
                            $row = $query->getRow();
                            $lastId = $row->intern_id; // e.g., 'GZI09'

                            // Extract numeric part
                            $number = intval(substr($lastId, 3));
                            $newNumber = $number + 1;

                            $newInternId = 'GZI' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
                        } else {
                            $newInternId = 'GZI01';
                        }
                        $newCode = $newInternId;

                        $interndata = [
                            'intern_id' => $newInternId,
                            'name' => $exist_user[0]['name'],
                            'first_name' => $exist_user[0]['first_name'],
                            'last_name' =>  $exist_user[0]['last_name'],
                            'initial' =>  $exist_user[0]['initial'],
                            'college' => $exist_user[0]['college'],
                            'course' => $exist_user[0]['course_name'],
                            'personal_mail' => $exist_user[0]['personal_mail'],
                            'phone_no' => $exist_user[0]['phone_no'],
                            'resume' => $exist_user[0]['resume'],
                            'dob' => $exist_user[0]['dob'],
                            'department_id' => $exist_user[0]['department'],
                            'mode_of_apply' => $exist_user[0]['mode_of_apply'],
                            'gender' => $exist_user[0]['gender'],
                            'join_date' => $dateofupdate,
                            'designation' => $exist_user[0]['designation'],
                        ];

                        $saved_intern = $db->table('interns')->insert($interndata);
                        if (!$saved_intern) {
                            return $this->response->setJSON(['status' => 'error', 'message' => 'Intern saved Failed.']);
                        }
                    } else {
                        $employee_save = [
                            'emp_id' => $newCode,
                            'role' => '3',
                            'name' => $exist_user[0]['name'],
                            'first_name' => $exist_user[0]['first_name'],
                            'last_name' =>  $exist_user[0]['last_name'],
                            'initial' =>  $exist_user[0]['initial'],
                            'attendance_id' => (int)$incremented,
                            'dob' => $dob,
                            'doj' => $dateofupdate,
                            'gender' => $exist_user[0]['gender'],
                            'age' => $age,
                            'personal_mail' => $exist_user[0]['personal_mail'],
                            'phone_no' => $exist_user[0]['phone_no'],
                            'dept' => $exist_user[0]['department'],
                            'source_hire' => $exist_user[0]['mode_of_apply'],
                            'exprience' => $exist_user[0]['experience'],
                            'designation' => $role,
                            'emp_status' => 1
                        ];

                        $db->table('employees')->insert($employee_save);
                    }
                    $update = [
                        'selected_note' => $scoreornote,
                        'last_action_date' => $dateofupdate,
                        'gighz_id' => $newCode,
                        'designation' => $role,
                    ];
                } else {
                    $update = [
                        'selected_note' => $scoreornote,
                        'last_action_date' => $dateofupdate,
                        'designation' => $role,
                    ];
                }
            } elseif ($status == 'rejected') {
                $update = [
                    'rejected_note' => $scoreornote,
                    'last_action_date' => $dateofupdate,
                ];
            }

            $update['requirment_status'] = $status;
            $update['next_round'] = $scheduledType;
            $update['scheduled_date'] = $scheduledDate;

            $db->table('recruitment')->update($update, ['recruitment_id' => $id]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Recruitment updated.', 'data' => $update]);
        }
    }


    public function getRquirementDetails()
    {
        $db = db_connect();

        $data = $db->query("SELECT * FROM recruitment ORDER BY updated_at DESC")->getResultArray();
        return $this->response->setJSON($data);
    }

    public function studentdetails($id)
    {
        $db = db_connect();

        $data['id'] = $id;

        $data['data'] = $db->query("SELECT r.*, d.dept_name FROM recruitment r join department d ON d.dept_id = r.department where recruitment_id = ?", [$id])->getResultArray();

        return view('hrms/studentdetails', $data);
    }

    public function getstudeteditdetails($id)
    {
        $db = db_connect();


        $data['user'] = $db->query("SELECT r.*, d.dept_name FROM recruitment r join department d ON d.dept_id = r.department where recruitment_id = ?", [$id])->getResultArray();

        $data['department'] = $db->query("SELECT dept_name, dept_id FROM department")->getResultArray();


        return $this->response->setJSON($data);
    }

    public function getpositions($id)
    {
        $db = db_connect();

        $data = $db->query("SELECT department FROM recruitment WHERE recruitment_id = ?", [$id])->getResultArray();

        $department = $data[0]['department'];

        $position = $db->query("SELECT position_id, position_name FROM tbl_position WHERE position_dept_name = ?", [$department])->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $position]);
    }


    public function test()
    {
        $dob = '200';
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;
        echo $age;
    }

    // update student details
    public function updatestudentdetails($id)
    {
        helper(['form', 'filesystem']);

        $db = db_connect();

        $request = $this->request;

        $firstname = $request->getPost('edit-firstname');
        $lastname = $request->getPost('edit-lastname');
        $initial = $request->getPost('edit-initial');

        $data = [
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'initial'   => $initial,
            'dob'   => $request->getPost('edit-dob'),
            'name'  => trim($firstname . ' ' . $lastname . ' ' . $initial),
            'college' => $request->getPost('edit-college'),
            'course_name' => $request->getPost('edit-course'),
            'personal_mail' => $request->getPost('edit-mail'),
            'phone_no' => $request->getPost('edit-phone'),
            'department' => $request->getPost('edit-dept'),
            'mode_of_apply' => $request->getPost('edit-moa'),
            'gender' => $request->getPost('edit-gender') ?? '',
            'experience' => $request->getPost('edit-exp') ?? '',
            'employment_type' => $request->getPost('edit-employment-type') ?? '',
        ];

        $updated = $db->table("recruitment")->set($data)->where('recruitment_id', $id)->update();
        if ($updated) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Student details updated successfullly']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Student details updated fail']);
        }
    }

    public function sendofferletter()
    {
        $db = db_connect();

        $request = $this->request;

        $sal = $request->getPost('offer-salary');
        $doj = $request->getPost('offer-doj');
        $note = $request->getPost('offer-note');
        $emp_id = $request->getPost('offer-emp_id');
        $type = $request->getPost('offer-type');

        // return $this->response->setJSON($request->getPost());

        // return $this->response->setJSON(['data' => "$sal - $doj -$note - $emp_id - $type"]);
        if ($type === '1') {
            $employee_data = $db->query("SELECT * FROM employees WHERE emp_id =? LIMIT 1", [$emp_id])->getResultArray();
            $mailid = 'lajeni3349@amcret.com' ?? $employee_data[0]['personal_mail'];
            $name = $employee_data[0]['name'];
            $designation_table = $db->query("SELECT position_name FROM tbl_position WHERE position_id = ?", [$employee_data[0]['designation']])->getResultArray();

            $position = $designation_table[0]['position_name'];

            $increment = [
                'increment_employee' => $emp_id,
                'previous_pay' => 00,
                'increment_amount' => $sal,
                'new_pay' => $sal,
                'increment_note' => 'New Joiner',
                'increment_date' => date('Y-m-d'),
            ];


            $exist_increment = $db->query("SELECT * FROM tbl_increment WHERE increment_employee = ? AND increment_note = ?", [$emp_id, 'New Joiner'])->getResultArray();
            if (!empty($exist_increment)) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'You already sent offer letter']);
            } else {

                $to = $mailid;
                $subject = 'GigHz IT solution Offer Letter';
                $message = "
                        Dear $name,

                        We are pleased to offer you the position at GigHz IT Solution. After evaluating your profile and skills, we believe you will be a valuable addition to our team.

                        Here are the details of your offer:

                        Employment Type: $position
                        Joining Date: $doj  
                        " . (isset($salary) ? "Salary: $salary\n" : "") . "

                        Please confirm your acceptance of this offer by replying to this email or contacting our HR department.

                        We look forward to welcoming you aboard.

                        Note: 

                        " . $note . "

                        Best regards,  
                        GigHz IT Solution HR Team
                        ";

                $this->connecthrmail($to, $subject, $message);

                if ($db->table("tbl_increment")->insert($increment)) {
                    return $this->response->setJSON(['status' => 'success', 'msg' => 'Offer letter send successfully']);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'msg' => 'Offer letter send faild']);
                }
            }
        } else {

            $employee_data = $db->query("SELECT * FROM interns WHERE intern_id = ? LIMIT 1", [$emp_id])->getResultArray();

            $mailid = 'lajeni3349@amcret.com' ?? $employee_data[0]['personal_mail'];
            $name = $employee_data[0]['name'];
            $designation_table = $db->query("SELECT position_name FROM tbl_position WHERE position_id = ?", [$employee_data[0]['designation']])->getResultArray();

            $position = $designation_table[0]['position_name'];

            $to = $mailid;
            $subject = 'GigHz IT solution Offer Letter';
            $message = "
                        Dear $name,

                        We are pleased to offer you the position at GigHz IT Solution. After evaluating your profile and skills, we believe you will be a valuable addition to our team.

                        Here are the details of your offer:

                        Employment Type: $position
                        Joining Date: $doj  

                        Please confirm your acceptance of this offer by replying to this email or contacting our HR department.

                        We look forward to welcoming you aboard.

                        Best regards,  
                        GigHz IT Solution HR Team
                        ";

            $this->connecthrmail($to, $subject, $message);

            return $this->response->setJSON(['status' => 'success', 'msg' => 'Offer letter send successfully']);
        }
    }

    public function connecthrmail($to, $subject, $message)
    {
        $email = \Config\Services::email();
        $email->clear(true);

        // $to = 'magendiran.m@gighz.net';

        // Custom SMTP config directly in function
        $config = [
            'protocol'    => 'smtp',
            'SMTPHost'    => 'smtp.hostinger.com',
            'SMTPUser'    => 'hr@gighz.net',
            'SMTPPass'    => 'GigHz123#',
            'SMTPPort'    => 587, // or 465 if using SSL
            'SMTPCrypto'  => 'tls', // or 'ssl'
            'mailType'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
        ];

        $email->initialize($config);

        // Compose email
        $email->setFrom('hr@gighz.net', 'HR');
        $email->setTo($to);
        // $email->setCC('chandra.mohan@gighz.net');
        $email->setSubject($subject);
        $email->setMessage($message);

        // Send email
        if ($email->send()) {
            return;
        } else {
            echo 'Email failed to send.<br>';
            echo $email->printDebugger(['headers']);
        }
    }


    // register employees

    public function employeeRegister()
    {
        $interns = $this->db->query("SELECT id, name FROM interns WHERE status = 1")->getResultArray();
        return view('hrms/register_employees', ['interns' => $interns]);
    }

    public function getInternDetails($id)
    {
        $query = $this->db->query("SELECT * FROM interns WHERE id = ?", [$id]);
        $intern = $query->getRowArray();

        return $this->response->setJSON($intern);
    }

    // ------------------------------------------------------------------------------------



    public function uploadDocuments()
    {
        $db = db_connect();

        $internId  = $this->request->getPost('intern_id');
        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        $note      = $this->request->getPost('note');
        $files     = $this->request->getFileMultiple('intern_documents'); // ✅ FIX

        // Get intern info
        $intern     = $db->query("SELECT name, document_list_file FROM interns WHERE intern_id = ?", [$internId])->getRowArray();
        $internName = $intern['name'] ?? 'Unknown';

        // Update intern's join and exit dates
        $db->query("UPDATE interns SET join_date = ?, exit_date = ? WHERE intern_id = ?", [
            $startDate,
            $endDate,
            $internId
        ]);

        // Existing documents (already stored in DB)
        $existingDocs = [];
        if (!empty($intern['document_list_file'])) {
            $existingDocs = json_decode($intern['document_list_file'], true);
        }

        // Collect uploaded filenames
        $uploadedNames = $existingDocs;

        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $customName = $file->getClientName(); // renamed via JS (prompt)

                    $uploadPath = FCPATH . 'uploads/intern_doc/' . $internId . '/joining/';

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $targetPath = $uploadPath . $customName;
                    $fileExt    = pathinfo($customName, PATHINFO_EXTENSION);
                    $fileBase   = pathinfo($customName, PATHINFO_FILENAME);
                    $counter    = 1;

                    while (file_exists($targetPath)) {
                        $customName = $fileBase . '_' . $counter . '.' . $fileExt;
                        $targetPath = $uploadPath . $customName;
                        $counter++;
                    }

                    $file->move($uploadPath, $customName);

                    $uploadedNames[] = $customName;
                }
            }
        }

        if (!empty($uploadedNames)) {
            $db->query("UPDATE interns SET document_list_file = ? WHERE intern_id = ?", [
                json_encode($uploadedNames),
                $internId
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Documents uploaded successfully',
                'files'   => $uploadedNames
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'No files uploaded'
            ]);
        }
    }

    // ====================================================================================================

    // DONLOAD LIST OF DOCUMENTS

    public function downloadInternDocx($internId)
    {

        $db = db_connect();

        // Fetch intern details
        $intern = $db->table('interns')
            ->select('interns.*, tbl_position.position_name, department.dept_name')
            ->join('tbl_position', 'tbl_position.position_id = interns.designation')
            ->join('department', 'department.dept_id = interns.department_id')
            ->where('intern_id', $internId)
            ->get()
            ->getRowArray();

        if (!$intern) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Intern not found");
        }

        // Parse files
        $files = [];
        if (!empty($intern['document_list_file'])) {
            $files = json_decode($intern['document_list_file']);
        }

        $docList = "";
        $total_doc = 0;
        foreach ($files as $i => $doc) {
            $total_doc++;
            $docList .= ($i + 1) . ". " . trim($doc) . "<w:br/>";
        }

        // Load template
        $templatePath = WRITEPATH . 'templates/hr/04-Document submitted by candidates 1.docx';
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found");
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Replace placeholders
        $templateProcessor->setValue('emp_name', $intern['name'] ?? '');
        $templateProcessor->setValue('emp_education', $intern['course'] ?? '');
        $templateProcessor->setValue('emp_doj', $intern['join_date'] ?? '');
        $templateProcessor->setValue('emp_designation', $intern['position_name'] ?? '');
        $templateProcessor->setValue('emp_dept', $intern['dept_name'] ?? '');
        $templateProcessor->setValue('document_list', $docList);
        $templateProcessor->setValue('total_doc', $total_doc);

        // Save to temp
        $outputPath = WRITEPATH . 'temp/intern_' . $internId . '.docx';
        $templateProcessor->saveAs($outputPath);

        // Force download
        return $this->response->download($outputPath, null)
            ->setFileName("intern_{$internId}.docx");
    }



    // GET EMPLOYEE LIST FILES

    public function getFiles()
    {
        $internId = $this->request->getGet('intern_id');
        $doc = $this->db->table('interns')
            ->where('intern_id', $internId)
            ->get()
            ->getRowArray();

        $files = [];
        if (!empty($doc['document_list_file'])) {
            // Decode JSON into array
            $files = json_decode($doc['document_list_file'], true) ?? [];
        }

        return $this->response->setJSON(['files' => $files]);
    }
    // DELETE INTERN FILE LIST

    public function deleteFile()
    {
        $internId = $this->request->getPost('intern_id');
        $fileName = $this->request->getPost('file');

        $db = db_connect();
        $internModel = $db->table('interns');
        $intern = $internModel->where('intern_id', $internId)->get()->getRowArray();

        if (!$intern) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Intern not found'
            ]);
        }

        // Get current file list
        $files = json_decode($intern['document_list_file'], true) ?? [];

        // Remove the file from array
        $updatedFiles = array_filter($files, function ($f) use ($fileName) {
            return $f !== $fileName;
        });

        // Save updated array back (fix: use intern_id instead of id)
        $internModel
            ->set(['document_list_file' => json_encode(array_values($updatedFiles))])
            ->where('intern_id', $internId)
            ->update();

        // Delete actual file
        $filePath = FCPATH . "uploads/intern_doc/{$internId}/joining/{$fileName}";
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'File deleted successfully'
        ]);
    }

    // ==================================================================================================


    public function getDepartments()
    {
        $db = db_connect();
        $departments = $db->query("SELECT dept_id, dept_name FROM department")->getResultArray();
        return $this->response->setJSON($departments);
    }

    public function getEmployeesByDepartment($deptId)
    {
        $db = db_connect();
        $employees = $db->query("SELECT emp_id, name FROM employees WHERE dept = ? and emp_status = '1'", [$deptId])->getResultArray();
        return $this->response->setJSON($employees);
    }

    public function getEmployeeDetails($id)
    {
        $db = db_connect();
        $emp = $db->query("SELECT * FROM employees WHERE emp_id = ?", [$id])->getRowArray();
        return $this->response->setJSON($emp);
    }

    // update employee details
    public function updateEmployee()
    {
        $db = db_connect();

        $data = $this->request->getPost();
        $no = $data['emp_id'];
        // unset($data['no']); // don't update primary key

        $columns = array_keys($data);
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", $columns));
        $values = array_values($data);
        $values[] = $no;

        $sql = "UPDATE employees SET $setClause WHERE emp_id = ?";
        $db->query($sql, $values);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function getRoles()
    {
        $db = db_connect();
        $roles = $db->query("SELECT id, user_name FROM role WHERE user_status = 1")->getResultArray();
        return $this->response->setJSON($roles);
    }

    public function getDesignations()
    {
        $db = db_connect();
        $positions = $db->query("SELECT position_id, position_name FROM tbl_position WHERE position_status = 1")->getResultArray();
        return $this->response->setJSON($positions);
    }

    // ---------------------------------------------------------------------------------------------------

    // EMPLOYEE DOCUMENTS

    public function uploadEmployeeDocs()
    {
        $emp_id = $this->request->getPost('employee_id');
        $files  = $this->request->getFiles();

        // return $this->response->setJSON(['data' => $this->request->getPost(), 'files' => $this->request->getFiles()]);

        if (!$emp_id || empty($files['documents'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'No files uploaded'
            ]);
        }

        $db = db_connect();

        // Get existing file names from DB
        $existingRow = $db->table('employees')
            ->where('emp_id', $emp_id)
            ->get()
            ->getRowArray();
        $mail_msg = 'Mail Not sent';
        if (isset($existingRow['doc_updates']) && $existingRow['doc_updates'] > 0) {
            $to = "lajeni3349@amcret.com";
            $sub = "Updated Employee Documents";
            $message = "Reworked Employee documets";

            send_email($to, $sub, $message);
            $mail_msg = "Mail Seded";
        }

        $existingFiles = [];
        if ($existingRow && !empty($existingRow['documents'])) {
            $existingFiles = json_decode($existingRow['documents'], true) ?? [];
        }

        $uploadedNames = [];

        foreach ($files['documents'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getClientName();
                $newName = $originalName;
                $file->move(FCPATH . 'uploads/employee_doc/' . $emp_id . '/' . 'joining/', $newName);
                $uploadedNames[] = $originalName; // store original name for display
            }
        }

        // Merge old + new
        $allFiles = array_merge($existingFiles, $uploadedNames);

        // Save back to DB
        if ($existingRow) {
            $db->table('employees')
                ->set(['documents' => json_encode($allFiles), 'doc_updates' => $existingRow['doc_updates'] + 1])
                ->where('emp_id', $emp_id)
                ->update();
        } else {
            $db->table('employees')->insert([
                'emp_id'    => $emp_id,
                'documents' => json_encode($allFiles)
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'emp_id' => $emp_id,
            'files'  => $allFiles,
            'mail' => $mail_msg
        ]);
    }

    // -----------------------------------------------------------------------------------------
    // GENERATE EMPLOYEE DOCUMENTS

    public function generateEmployeeDocList($emp_id)
    {
        $db = db_connect();

        // Get employee's documents
        $employee = $db->table('employees')
            ->select('employees.*, department.dept_name, tbl_position.position_name')
            ->join('tbl_position', 'tbl_position.position_id = employees.designation')
            ->join('department', 'department.dept_id = employees.dept')
            ->where('emp_id', $emp_id)
            ->get()
            ->getRowArray();

        if (!$employee || empty($employee['documents'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'No documents found for this employee'
            ]);
        }

        $docs = json_decode($employee['documents'], true);

        // Load Word template
        $templatePath = WRITEPATH . "templates/hr/04-Document submitted by candidates 1.docx";
        $templateProcessor = new TemplateProcessor($templatePath);

        // Example: Replace placeholder with list of documents
        $docList = "";
        $total_doc = 0;
        foreach ($docs as $i => $doc) {
            $total_doc++;
            $docList .= ($i + 1) . ". " . $doc . "\n";
        }

        $templateProcessor->setValue('emp_name', $employee['name']);
        $templateProcessor->setValue('emp_education', $employee['course_name']);
        $templateProcessor->setValue('emp_doj', $employee['doj']);
        $templateProcessor->setValue('emp_designation', $employee['position_name']);
        $templateProcessor->setValue('emp_dept', $employee['dept_name']);
        $templateProcessor->setValue('document_list', $docList);
        $templateProcessor->setValue('total_doc', $total_doc);

        // Save new file
        $outputFile = FCPATH . "uploads/employee_doc/" . $emp_id . "/joining/" . $emp_id . "_Document_List.docx";
        $templateProcessor->saveAs($outputFile);

        // Download
        return $this->response->download($outputFile, null)->setFileName($emp_id . "_Document_List.docx");
    }

    // -----------------------------------------------------------------------------

    public function getEmployeeDocs($emp_id)
    {
        $db = db_connect();

        $docRow = $db->table('employees')
            ->where('emp_id', $emp_id)
            ->get()
            ->getRowArray();

        if (!$docRow) {
            return $this->response->setJSON([
                'status' => 'error',
                'docs'   => [],
                'msg'    => 'No documents found'
            ]);
        }

        // Decode stored JSON array
        $docs = json_decode($docRow['documents'], true) ?? [];

        return $this->response->setJSON([
            'status' => 'success',
            'docs'   => $docs
        ]);
    }

    public function deleteEmployeeDoc($emp_id, $filename)
    {
        $folder = FCPATH . 'uploads/employee_doc/' . $emp_id . '/' . 'joining/';
        $filePath = $folder . $filename;

        if (file_exists($filePath)) {
            unlink($filePath);

            // Update DB
            $db = db_connect();
            $row = $db->table('employees')->where('emp_id', $emp_id)->get()->getRowArray();
            $docs = json_decode($row['documents'], true) ?? [];
            $docs = array_diff($docs, [$filename]);
            $db->table('employees')->set(['documents' => json_encode(array_values($docs))])->where('emp_id', $emp_id)->update();

            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'msg' => 'File not found']);
    }

    public function downloadSubmittedDocs($emp_id)
    {
        $db = db_connect();

        // Get employee and uploaded docs
        $employee = $db->table('employees')->where('emp_id', $emp_id)->get()->getRowArray();
        // $docRow   = $db->table('employee_documents')->where('emp_id', $emp_id)->get()->getRowArray();

        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("No documents found for this employee.");
        }

        $docs = json_decode($employee['documents'], true) ?? [];

        // Format document list
        $docList = "";
        foreach ($docs as $i => $docName) {
            $docList .= ($i + 1) . ". " . $docName . "\n";
        }

        // Load template
        $templatePath = WRITEPATH . 'templates/hr/Employee_Submitted_Docs.docx';
        $template = new TemplateProcessor($templatePath);

        // Replace placeholders
        $template->setValue('employee_name', $employee['name']);
        $template->setValue('submitted_docs', trim($docList));

        // Save to temp file
        $outputFile = WRITEPATH . 'exports/hr/SubmittedDocs_' . $employee['name'] . '.docx';
        $template->saveAs($outputFile);

        // Download
        return $this->response
            ->download($outputFile, null)
            ->setFileName('SubmittedDocs_' . $employee['name'] . '.docx');
    }

    // --------------------------------------------------------------------------------------------------------
    // POLICY MANAGEMENT

    // Policy
    public function policyManagementPage()
    {
        return view('hrms/policy_management');
    }

    public function list()
    {
        $db = db_connect();
        $query = $db->query("SELECT * FROM gighz_policy ORDER BY id DESC");
        return $this->response->setJSON(['data' => $query->getResultArray()]);
    }

    public function policies()
    {
        $db = db_connect();
        $query = $db->query("SELECT * FROM gighz_policy ORDER BY id DESC");
        return $this->response->setJSON(['data' => $query->getResultArray()]);
    }

    public function policyNames()
    {
        $names = ['Leave Policy', 'Data Policy', 'HR Policy', 'IT Security Policy'];
        return $this->response->setJSON($names);
    }

    public function policyLastVersion($policyName)
    {
        $db = db_connect();
        $query = $db->query("SELECT version FROM gighz_policy WHERE policy_name = ? ORDER BY id DESC LIMIT 1", [$policyName])->getRowArray();
        $row = $query['version'] ?? '0.0.0';

        return $this->response->setJSON(['data' => $row]);
    }

    public function savePolicy()
    {
        $rules = [
            'policy_name' => 'required',
            'version'     => 'required',
            'status'      => 'required',
            'short_note'  => 'required',
            'content'     => 'required',
            'document'    => 'uploaded[document]|mime_in[document,application/pdf]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => $this->validator->getErrors()]);
        }

        $file = $this->request->getFile('document');
        $docName = $file->getRandomName();
        $uploadPath = FCPATH . 'uploads/policies/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $file->move($uploadPath, $docName);

        $data = [
            'policy_name'   => $this->request->getPost('policy_name'),
            'version'       => $this->request->getPost('version'),
            'status'        => $this->request->getPost('status'),
            'short_note'    => $this->request->getPost('short_note'),
            'content'       => $this->request->getPost('content'),
            'document_path' => base_url('uploads/policies/' . $docName),
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $db = db_connect();
        $builder = $db->table('gighz_policy');
        $builder->insert($data);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deletePolicy($id)
    {
        $db = db_connect();

        if ($db->table('gighz_policy')->where('id', $id)->delete()) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error']);
        }
    }

    public function updatePolicy($id)
    {
        $db = db_connect();

        $data = [
            'status'     => $this->request->getPost('status'),
            'short_note' => $this->request->getPost('short_note'),
            'content'    => $this->request->getPost('content'),
        ];

        if ($db->table('gighz_policy')->set($data)->where('id', $id)->update()) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error']);
        }
    }


    // Carrear page 
    public function career()
    {
        return view('hrms/career');
    }

    public function listCareers()
    {
        $query = $this->db->query("
                                        SELECT 
                                            c.*, 
                                            DATE(c.created_at) as created, 
                                            p.position_name, 
                                            d.dept_name 
                                        FROM careers c 
                                        JOIN department d ON d.dept_id = c.dept_id 
                                        JOIN tbl_position p ON p.position_id = c.role_id 
                                        WHERE c.active = 1
                                    ")->getResultArray();
        $data['data'] = $query;
        $data['department'] = $this->db->query("SELECT dept_id, dept_name FROM department")->getResultArray();
        $data['role']        = $this->db->query("SELECT position_id, position_name FROM tbl_position WHERE position_status = 1")->getResultArray();

        return $this->response->setJSON($data);
    }
    public function addCareer()
    {
        $post = $this->request->getPost();
        $data = [
            'dept_id'    => $post['department'],
            'role_id'    => $post['role'],
            'description' => $post['description'],
            'count'      => $post['count'],
            'package'    => $post['package'],
            'active'     => isset($post['active']) ? 1 : 0,
        ];
        $this->db->table('careers')->insert($data);
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function editCareer()
    {
        $post = $this->request->getPost();
        $id = $post['id'];
        $data = [
            'dept_id'    => $post['department'],
            'role_id'    => $post['role'],
            'description' => $post['description'],
            'count'      => $post['count'],
            'package'    => $post['package'],
            'active'     => isset($post['active']) ? 1 : 0,
        ];
        $this->db->table('careers')->where('id', $id)->update($data);
        return $this->response->setJSON(['status' => 'updated']);
    }

    // DISSIPLANARY ACTION

    public function disciplinary()
    {
        return view('hrms/disciplinary');
    }

    public function listDisciplinary()
    {
        $records = $this->db->table('disciplinary_actions')
            ->select('disciplinary_actions.*, employees.name')
            ->join('employees', 'disciplinary_actions.emp_id = employees.emp_id')
            ->where('active', 1)
            ->get()
            ->getResultArray();
        return $this->response->setJSON($records);
    }

    public function addDisciplinary()
    {
        $post = $this->request->getPost();
        $file = $this->request->getFile('apology_doc');
        $filename = null;
        if ($file && $file->isValid()) {
            $filename = $_POST['emp_id'] . "_Disciplinary";
            $file->move(FCPATH . 'uploads/employee_doc/' . $post['emp_id'] . '/serving/disciplinary/', $filename);
        }

        $data = [
            'action_date'  => $post['action_date'],
            'emp_id'       => $post['emp_id'],
            'reason'       => $post['reason'],
            'apology_doc'  => $filename,
            'action_taken' => $post['action_taken'],
            'active'       => 1
        ];
        $this->db->table('disciplinary_actions')->insert($data);
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function editDisciplinary()
    {
        $post = $this->request->getPost();
        $id = $post['id'];
        $exist_doc = $this->request->getFile('existing_doc') ?? '';
        $file = $this->request->getFile('apology_doc') ?? $exist_doc;
        $filename = $post['existing_doc'] ?? null;
        if ($file && $file->isValid()) {
            $filename = $post['emp_id'] . '_Disciplinary';
            $file->move(FCPATH . 'uploads/employee_doc/' . $post['emp_id'] . '/serving/disciplinary/', $filename);
        }

        $data = [
            'action_date'  => $post['action_date'],
            'emp_id' => $post['emp_id'],
            'reason'       => $post['reason'],
            'apology_doc'  => $filename,
            'action_taken' => $post['action_taken'],
        ];
        $this->db->table('disciplinary_actions')->where('id', $id)->update($data);
        return $this->response->setJSON(['status' => 'updated']);
    }

    public function getUsers()
    {
        $employees = $this->db->table('employees')->select('emp_id, name')->where('emp_status', '1')->get()->getResultArray();
        return $this->response->setJSON($employees);
    }

    // ----------------------------------------------------------------------------------------------------

    // APPRAISAL

    public function appraisal_index()
    {
        $db = db_connect();

        $departments = $db->table('department')->get()->getResultArray();
        $employees   = $db->table('employees')->get()->getResultArray();

        return view('hrms/all_appraisal', [
            'departments' => $departments,
            'employees'   => $employees
        ]);
    }

    public function getAppraisalData()
    {

        $db = db_connect();
        $query = $db->table('employee_appraisal')
            ->select('department.dept_name, employees.name, employee_appraisal.*')
            ->join('employees', 'employees.emp_id = employee_appraisal.emp_id')
            ->join('department', 'department.dept_id = employee_appraisal.department')
            ->orderBy('employee_appraisal.date', 'desc')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $query]);
    }

    public function storeappraisal()
    {
        $db = db_connect();
        $data = [
            'department'  => $this->request->getPost('department'),
            'emp_id'      => $this->request->getPost('emp_id'),
            'date' => $this->request->getPost('review_date'),
            'stages'      => 'initiated'
        ];

        $db->table('employee_appraisal')->insert($data);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function appraisal_details($id)
    {
        $db = db_connect();

        $query = $db->table('employee_appraisal')
            ->select('department.dept_name, employees.name, employee_appraisal.*')
            ->join('employees', 'employees.emp_id = employee_appraisal.emp_id')
            ->join('department', 'department.dept_id = employee_appraisal.department')
            // ->join('tbl_increment', 'tbl_increment.increment_id = employee_appraisal.salary')
            ->where('employee_appraisal.id', $id)
            ->get()
            ->getResultArray();

        if (!empty($query[0]['salary'])) {
            $sal = $db->query("select new_pay from tbl_increment where increment_id = ?", [$query[0]['salary']])->getResultArray();
            $salary = $sal[0]['new_pay'];
        } else {
            $salary = 0;
        }

        // return $this->response->setJSON(['data' => $query]);

        return view('hrms/appraisal_details', ['data' => $query, 'salary' => $salary]);
    }

    public function updateswot($id)
    {

        $data =  $this->request->getPost();

        if (!empty($data)) {
            $updated = $this->db->table('employee_appraisal')->set($data)->where('id', $id)->update();
            if ($updated) {
                return $this->response->setJSON(['status' => 'success', 'msg' => 'SWOT Updated Successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'SWOT Updated Failed.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'filed to get form data.']);
        }
    }

    public function uploadappraisaldocuments($id, $emp_id)
    {
        $type = $this->request->getPost('document_type'); // e.g. 'nda_doc'
        $file = $this->request->getFile('document_input');

        if ($file && $file->isValid() && !$file->hasMoved()) {

            // Map your type to a readable prefix
            $prefixMap = [
                'nda_doc'          => 'NDA',
                'agreement_doc'    => 'AGREEMENT',
                'r_and_r_doc'      => 'R_AND_R',
                'other_doc'        => 'OTHER',
                'emp_feedback_doc' => 'EMP_FEEDBACK'
            ];
            $prefix = isset($prefixMap[$type]) ? $prefixMap[$type] : strtoupper($type);

            // Get today's date
            $dateStr = date('Ymd');

            // Preserve the extension (lowercase)
            $ext = strtolower($file->getClientExtension());

            // Build file name: PREFIX_ID_DATE.EXT
            $newName = "{$prefix}_{$emp_id}_{$dateStr}.{$ext}";

            // Move the file to uploads directory
            $file->move(FCPATH . "uploads/employee_doc/$emp_id/serving/appraisal", $newName, true); // overwrite if exists

            // "type => file" mapping for DB
            $data = [
                $type => $newName
            ];

            // Save to DB
            $db = \Config\Database::connect();
            $update = $db->table('employee_appraisal')
                ->set($data)
                ->where('id', $id)
                ->update();

            if ($update) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'msg'    => 'File Uploaded Successfully.',
                    'file'   => $newName
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'File Upload Failed.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'Invalid file or no file uploaded.'
        ]);
    }

    // public function getappraisalSalary($id){
    //     $this-
    // }

    // APPRAISAL SALARY

    public function updateSalary($id)
    {
        $db = db_connect();

        $emp_id = $this->request->getPost('emp_id');
        $salary = $this->request->getPost('salary');
        $mom    = $this->request->getPost('mom');

        if (!$emp_id || !$salary || !$mom) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Missing required fields'
            ]);
        }

        // Fetch existing appraisal
        $appraisal_sal_exist = $db->query(
            "SELECT * FROM employee_appraisal WHERE id = ? LIMIT 1",
            [$id]
        )->getRowArray();

        // Fetch last salary record
        $last_sal = $db->query(
            "SELECT * FROM tbl_increment WHERE increment_employee = ? ORDER BY increment_date DESC LIMIT 1",
            [$emp_id]
        )->getRowArray();

        $last_amount = $last_sal['previous_pay'] ?? 0;
        $increment   = $salary - $last_amount;

        if (empty($appraisal_sal_exist['salary'])) {
            // Insert new salary record
            $db->transStart();

            $db->table('tbl_increment')->insert([
                'increment_employee' => $emp_id,
                'new_pay'            => $salary,
                'previous_pay'       => $last_amount,
                'increment_amount'   => $increment,
                'increment_note'     => $mom,
                'increment_date'     => date('Y-m-d')
            ]);

            $salary_id = $db->insertID();

            $db->table('employee_appraisal')
                ->set([
                    'salary' => $salary_id,
                    'mom'    => $mom
                ])
                ->where('id', $id)
                ->update();

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg'    => 'Database update failed'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'msg'    => 'Salary & MOM saved successfully'
            ]);
        } else {
            // Update existing salary record
            $db->transStart();

            $db->table('tbl_increment')
                ->set([
                    'increment_note'   => $mom,
                    'new_pay'          => $salary,
                    'previous_pay'     => $last_amount,
                    'increment_amount' => $increment
                ])
                ->where('increment_id', $appraisal_sal_exist['salary'])
                ->update();

            $db->table('employee_appraisal')
                ->set(['mom' => $mom])
                ->where('id', $id)
                ->update();

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg'    => 'Database update failed'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'msg'    => 'Salary & MOM updated successfully'
            ]);
        }
    }


    // OTHER DOCUMENTS

    public function uploadOtherDocuments($id, $emp_id)
    {
        $db =db_connect();
        $builder = $db->table('employee_appraisal');

        // Get existing
        $existing = $builder->select('other_doc')->where('id', $id)->get()->getRowArray();
        $existingFiles = !empty($existing['other_doc']) ? json_decode($existing['other_doc'], true) : [];

        // $emp_id = !empty($existing['emp_id']) ? $existing['emp_id'] : NULL;

        // if($emp_id == NULL){
        //     return $this->response->setJSON(['status' => 'error', 'Empty Employee ID was coming.']);
        // }

        $files = $this->request->getFiles();
        $dateStr = date('Ymd');
        $uploaded = [];

        if (isset($files['files'])) {
            foreach ($files['files'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getName(); // Already custom from JS
                    $file->move(FCPATH . "uploads/employee_doc/$emp_id/serving/appraisal", $newName);
                    $uploaded[] = $newName;
                }
            }
        }

        $allFiles = array_merge($existingFiles, $uploaded);
        $builder->where('id', $id)->update(['other_doc' => json_encode($allFiles)]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function getOtherDocuments($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('employee_appraisal')->select('other_doc')->where('id', $id)->get()->getRowArray();
        $files = !empty($row['other_doc']) ? json_decode($row['other_doc'], true) : [];
        return $this->response->setJSON($files);
    }

    public function deleteOtherDocument($id)
    {
        $fileName = $this->request->getPost('file');

        $db = \Config\Database::connect();
        $builder = $db->table('employee_appraisal');
        $row = $builder->select('other_doc')->where('id', $id)->get()->getRowArray();
        $files = !empty($row['other_doc']) ? json_decode($row['other_doc'], true) : [];

        $files = array_filter($files, fn($f) => $f !== $fileName);

        // Delete from server
        $filePath = WRITEPATH . 'uploads/' . $fileName;
        if (is_file($filePath)) unlink($filePath);

        $builder->where('id', $id)->update(['other_doc' => json_encode(array_values($files))]);

        return $this->response->setJSON(['status' => 'deleted']);
    }


    // ------------------------------------------------------------------------------

    // STATUS CHANGE

    public function changeAppraisalstage($id)
    {
        $type = $this->request->getPost('stage_select') ?? null;
        $dateInput = $this->request->getPost('stages_date');
        $feedback = $this->request->getPost('feedback') ?? null;

        if (empty($type)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Select Stage']);
        }

        $data = ['stages' => $type]; // Initialize $data

        if ($type != 'document stage' && $type != 'completed') {
            if (empty($feedback)) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Enter Feedback']);
            }

            switch ($type) {
                case 'employee feedback':
                    $data['emp_feedback'] = $feedback;
                    $data['emp_feedback_date'] = $dateInput;
                    break;
                case '360 feedback':
                    $data['feedback_360'] = $feedback;
                    $data['feedback_360_date'] = $dateInput;
                    break;
                case 'md disscussion':
                    $data['md_discussion'] = $feedback;
                    $data['md_discussion_date'] = $dateInput;
                    break;
                case 'hold':
                    $data['hold_feedback'] = $feedback;
                    $data['hold_date'] = $dateInput;
                    break;
            }
        } else {
            if ($type == 'completed') {
                $data['completed_date'] = $dateInput;
            } elseif ($type == 'document stage') {
                $data['document_stage_date'] = $dateInput;
            }
        }

        if ($this->db->table('employee_appraisal')->where('id', $id)->update($data)) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Stage Updated Successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Failed to update stage.']);
        }
    }

    public function getlastappraisal($emp_id)
    {
        $query = $this->db->table('employee_appraisal')->select('id,date')->where('emp_id', $emp_id)->get()->getResultArray();
        return $this->response->setJSON($query);
    }


    // DOWNLOAD NAD

    public function downloadNDA($emp_id)
    {
        // Get employee data from DB
        $employee = $this->db->table('employees')
            ->where('emp_id', $emp_id)
            ->get()
            ->getRowArray();

        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Employee not found');
        }

        // Path to your NDA template
        $templatePath = WRITEPATH . 'templates/hr/03_NDA.docx';

        // Create TemplateProcessor
        $template = new TemplateProcessor($templatePath);

        // If your placeholders use {name} instead of ${name}
        // $template->setMacroOpeningChars('{');
        // $template->setMacroClosingChars('}');

        // Replace placeholders
        $template->setValue('name', $employee['name']);
        $template->setValue('date', date('d-m-Y'));

        // Create safe file name
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $employee['name']);
        $tempFile = WRITEPATH . 'exports/hr/NDA_' . $safeName . '.docx';

        // Save and download
        $template->saveAs($tempFile);

        return $this->response
            ->download($tempFile, null)
            ->setFileName('NDA_' . $safeName . '.docx');
    }


    // ---------------- 11-08-2025 ---------------add assets + exit page 
    // ---------------------------------------------------------------------------------------------------------------

    public function assets()
    {
        $db = db_connect();
        $departments = $db->query("SELECT * FROM department")->getResultArray();
        $employees = $db->query("SELECT emp_id, name FROM employees")->getResultArray();

        return view('hrms/assets', [
            'departments' => $departments,
            'employees' => $employees
        ]);
    }

    public function EmployeesByDepartment()
    {
        $deptId = $this->request->getGet('department');
        $db = db_connect();
        $employees = $db->query("SELECT emp_id, name FROM employees WHERE dept = ? AND emp_status='1' ", [$deptId])->getResultArray();
        return $this->response->setJSON($employees);
    }




    public function getEmployeeAssets()
    {
        $empId = $this->request->getGet('emp_id');
        $db = db_connect();
        $assets = $db->query("SELECT * FROM assets WHERE emp_id = ?", [$empId])->getResultArray();
        return $this->response->setJSON($assets);
    }

    public function saveAsset()
    {
        $data = $this->request->getPost();

        $db = db_connect();
        $db->query("INSERT INTO assets (asset_id, asset_name, description, emp_id ,date) VALUES (?, ?, ?, ?, ?)", [
            $data['asset_id'],
            $data['asset_name'],
            $data['description'],
            $data['emp_id'],
            $data['date']
        ]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Asset saved']);
    }

    public function updateAsset()
    {
        $data = $this->request->getPost();
        $db = db_connect();
        $db->query("UPDATE assets SET asset_id = ?, asset_name = ?, description = ? WHERE id = ?", [
            $data['asset_id'],
            $data['asset_name'],
            $data['description'],
            $data['id']
        ]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Asset updated']);
    }

    public function deleteAsset()
    {
        $id = $this->request->getPost('id');

        if ($id) {
            $db = db_connect();
            $db->query("DELETE FROM assets WHERE id = ?", [$id]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Asset deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid asset ID'
        ]);
    }


    // -------------------------------------------------------------------------------------------------------
    // Exit page 

    public function exitpage()
    {
        $db = db_connect();
        $departments = $db->query("SELECT * FROM department")->getResultArray();
        $employees = $db->query("SELECT emp_id, name FROM employees")->getResultArray();

        return view('hrms/exitpage', [
            'departments' => $departments,
            'employees' => $employees
        ]);
    }

    public function getExits()
    {
        $db = db_connect();
        $exits = $db->query("SELECT e.*, emp.name FROM hr_exit e JOIN employees emp ON e.emp_id = emp.emp_id")->getResultArray();
        return $this->response->setJSON($exits);
    }


    public function addExit()
    {
        $db = db_connect();
        $empId = $this->request->getPost('emp_id'); // or from DB if editing existing record

        $uploadPath = FCPATH . 'uploads/employee_doc/' . $empId . '/relive/'; // storage path: writable/uploads/exits/

        // Ensure folder exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Handle file uploads
        $mailDocFile = $this->request->getFile('mail_doc');
        $exitDocFile = $this->request->getFile('exit_doc');
        $clearanceDocFile = $this->request->getFile('clearance_doc');
        $currentDate = date('dmY'); // 09082025 format

        $mailDocName = null;
        $exitDocName = null;
        $clearanceDocName = null;
        // return $this->response->setJSON(['path' => $uploadPath]);

        //    return $this->response->setJSON([
        //     'name' => $mailDocFile->getName(),        // original file name
        //     'type' => $mailDocFile->getClientMimeType(), // mime type
        //     'size' => $mailDocFile->getSize(),        // size in bytes
        //     'tempPath' => $mailDocFile->getTempName() // temp file path
        // ]);

        if ($mailDocFile && $mailDocFile->isValid() && !$mailDocFile->hasMoved()) {
            $ext = $mailDocFile->getClientExtension(); // Get file extension
            $mailDocName = "mailDoc_{$empId}_{$currentDate}.{$ext}";
            $mailDocFile->move($uploadPath, $mailDocName);
        }

        if ($exitDocFile && $exitDocFile->isValid() && !$exitDocFile->hasMoved()) {
            $ext = $exitDocFile->getClientExtension();
            $exitDocName = "exitDoc_{$empId}_{$currentDate}.{$ext}";
            $exitDocFile->move($uploadPath, $exitDocName);
        }

        if ($clearanceDocFile && $clearanceDocFile->isValid() && !$clearanceDocFile->hasMoved()) {
            $ext = $clearanceDocFile->getClientExtension();
            $clearanceDocName = "clearanceDoc_{$empId}_{$currentDate}.{$ext}";
            $clearanceDocFile->move($uploadPath, $clearanceDocName);
        }
        // Save data in DB
        $data = [
            'exit_date'      => $this->request->getPost('exit_date'),
            // 'department_id'  => $this->request->getPost('department_id'),
            'emp_id'         => $this->request->getPost('emp_id'),
            'status'         => $this->request->getPost('status'),
            'reason'         => $this->request->getPost('reason'),
            'relieve_date'   => $this->request->getPost('relieve_date'),
            'exit_notes'     => $this->request->getPost('exit_notes'),
            'mail_doc'       => $mailDocName,
            'exit_doc'       => $exitDocName,
            'clearance_doc'  => $clearanceDocName
        ];

        if ($db->table('hr_exit')->insert($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Exit added successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Something Wrong'
            ]);
        }
    }


    public function deleteExit()
    {
        $db = db_connect();
        $id = $this->request->getPost('id');


        if ($db->table('hr_exit')->where('id', $id)->delete()) {
            return $this->response->setJSON([
                'message' => 'Exit Deleted',
                'status' => 'success'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Something Wrong'
            ]);
        }
    }


    public function getExitById($id)
    {
        $db = db_connect();
        $builder = $db->table('hr_exit');
        $query = $builder->getWhere(['id' => $id])->getRow();

        //   $db = db_connect();
        // $exits = $db->query("SELECT e.*, emp.name FROM hr_exit e JOIN employees emp ON e.emp_id = emp.emp_id")->getResultArray();
        // return $this->response->setJSON($exits);

        return $this->response->setJSON($query);
    }



    public function updateExit()
    {
        $id = $this->request->getPost('id');

        // Prepare upload path
        $empId = $this->request->getPost('emp_id'); // or from DB if editing existing record

        $uploadPath = FCPATH . 'uploads/employee_doc/' . $empId . '/relive/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Handle mail_doc upload
        $mailDocName = null;
        $mailDocFile = $this->request->getFile('mail_doc');
        if ($mailDocFile && $mailDocFile->isValid() && !$mailDocFile->hasMoved()) {
            $mailDocName = $mailDocFile->getRandomName();
            $mailDocFile->move($uploadPath, $mailDocName);
        }

        // Handle exit_doc upload
        $exitDocName = null;
        $exitDocFile = $this->request->getFile('exit_doc');
        if ($exitDocFile && $exitDocFile->isValid() && !$exitDocFile->hasMoved()) {
            $exitDocName = $exitDocFile->getRandomName();
            $exitDocFile->move($uploadPath, $exitDocName);
        }

        // Handle clearance_doc upload
        $clearanceDocName = null;
        $clearanceDocFile = $this->request->getFile('clearance_doc');
        if ($clearanceDocFile && $clearanceDocFile->isValid() && !$clearanceDocFile->hasMoved()) {
            $clearanceDocName = $clearanceDocFile->getRandomName();
            $clearanceDocFile->move($uploadPath, $clearanceDocName);
        }

        // Build update data array
        $data = [
            'emp_id'        => $this->request->getPost('emp_id'),
            // 'department_id' => $this->request->getPost('department_id'),
            'exit_date'     => $this->request->getPost('exit_date'),
            'reason'        => $this->request->getPost('reason'),
            'status'        => $this->request->getPost('status'),
            'relieve_date'  => $this->request->getPost('relieve_date'),
            'exit_notes'    => $this->request->getPost('exit_notes'),
        ];

        if ($mailDocName) {
            $data['mail_doc'] = $mailDocName;
        }
        if ($exitDocName) {
            $data['exit_doc'] = $exitDocName;
        }
        if ($clearanceDocName) {
            $data['clearance_doc'] = $clearanceDocName;
        }

        // Update in DB
        $db = \Config\Database::connect();
        $builder = $db->table('hr_exit');
        $builder->where('id', $id);
        $builder->update($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Exit updated successfully']);
    }
}
