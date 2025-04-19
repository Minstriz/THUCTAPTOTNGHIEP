<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Submission;
use App\Models\Score;
use App\Models\Student;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StudentAssignmentController extends Controller
{
    /**
     * Get list of available assignments for the student
     */
    public function getAssignments($student_id)
    {
        // Kiểm tra sinh viên tồn tại
        $student = Student::find($student_id);
        if (!$student) {
            return response()->json(['message' => 'Không tìm thấy sinh viên với ID: ' . $student_id], 404);
        }

        // Get classes the student is enrolled in
        $studentClasses = $student->studentClasses()
            ->where('status', 'Active')
            ->pluck('class_id');

        if ($studentClasses->isEmpty()) {
            return response()->json(['message' => 'Sinh viên này không đăng ký lớp học nào hoặc không có lớp học đang hoạt động.'], 404);
        }

        // Get assignments for those classes
        $assignments = Assignment::whereIn('sub_list_id', function ($query) use ($studentClasses) {
            $query->select('sub_list_id')
                ->from('list_question')
                ->whereIn('course_id', function ($subQuery) use ($studentClasses) {
                    $subQuery->select('course_id')
                        ->from('classroom')
                        ->whereIn('class_id', $studentClasses);
                });
        })->get();

        return response()->json($assignments);
    }

    /**
     * Get list of available exams for the student
     */
    public function getExams($student_id)
    {
        // Kiểm tra sinh viên tồn tại
        $student = Student::find($student_id);
        if (!$student) {
            return response()->json(['message' => 'Không tìm thấy sinh viên với ID: ' . $student_id], 404);
        }

        // Get classes the student is enrolled in
        $studentClasses = $student->studentClasses()
            ->where('status', 'Active')
            ->pluck('class_id');

        if ($studentClasses->isEmpty()) {
            return response()->json(['message' => 'Sinh viên này không đăng ký lớp học nào hoặc không có lớp học đang hoạt động.'], 404);
        }

        // Get exams that are related to the student's courses
        $exams = Exam::whereIn('sub_list_id', function ($query) use ($studentClasses) {
            $query->select('sub_list_id')
                ->from('list_question')
                ->whereIn('course_id', function ($subQuery) use ($studentClasses) {
                    $subQuery->select('course_id')
                        ->from('classroom')
                        ->whereIn('class_id', $studentClasses);
                });
        })->get();

        return response()->json($exams);
    }

    /**
     * Submit an assignment or exam
     */
    public function submitWorkAndAnswers(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'exam_id' => 'nullable|exists:exam,exam_id',
            'assignment_id' => 'nullable|exists:assignment,assignment_id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:question,question_id',
            'answers.*.answer_content' => 'required|string',
        ]);

        // Kiểm tra xem chỉ có 1 trong 2 ID (exam_id hoặc assignment_id) tồn tại
        if ((!$request->exam_id && !$request->assignment_id) || ($request->exam_id && $request->assignment_id)) {
            return response()->json(['message' => 'Bạn phải chọn hoặc bài thi hoặc bài tập, không thể cả hai!'], 400);
        }

        // Tạo Submission
        $submission = Submission::create([
            'submission_id' => Str::random(50), // ID ngẫu nhiên cho submission
            'student_id' => $request->student_id,
            'exam_id' => $request->exam_id,
            'assignment_id' => $request->assignment_id,
            'answer_file' => null, // Nếu có file đính kèm, xử lý sau
            'is_late' => false, // Kiểm tra trễ hạn nếu cần
            'temporary_score' => null,
            'created_at' => now(),
        ]);

        // 2. Cập nhật trạng thái bài kiểm tra hoặc bài tập
        if ($submission->exam_id) {
            DB::table('exam')
                ->updateOrInsert(
                    ['exam_id' => $submission->exam_id],
                    ['status' => 'Completed', 'updated_at' => now()]
                );
        }

        if ($submission->assignment_id) {
            DB::table('assignment')
                ->updateOrInsert(
                    ['assignment_id' => $submission->assignment_id],
                    ['status' => 'Completed', 'updated_at' => now()]
                );
        }

        // Lưu các câu trả lời
        $correctCount = 0;
        $total = count($request->answers);

        // Kiểm tra loại bài (exam hay assignment) và lấy type của nó
        $isExam = false;
        $isAssignment = false;
        $examType = null;
        if ($submission->exam_id) {
            $exam = Exam::find($submission->exam_id);
            if ($exam) {
                $isExam = true;
                $examType = $exam->type; // Lấy type của exam
            }
        } elseif ($submission->assignment_id) {
            $assignment = Assignment::find($submission->assignment_id);
            if ($assignment) {
                $isAssignment = true;
                $examType = $assignment->type; // Lấy type của assignment
            }
        }

        foreach ($request->answers as $answerData) {
            // Kiểm tra câu hỏi có tồn tại hay không
            $question = Question::find($answerData['question_id']);
            if (!$question) {
                return response()->json(['message' => 'Câu hỏi không tồn tại.'], 400);
            }

            // Kiểm tra loại bài: nếu là trắc nghiệm, thì chấm điểm
            if ($examType === 'Trắc nghiệm' && $isExam) {
                // Kiểm tra câu trả lời đúng cho bài thi trắc nghiệm
                $isCorrect = $question->options->firstWhere('is_correct', 1)?->option_text === $answerData['answer_content'];
                if ($isCorrect) {
                    $correctCount++;
                }
            }
            if ($examType === 'Trắc nghiệm' && $isAssignment) {
                // Kiểm tra câu trả lời đúng cho bài tập trắc nghiệm
                $isCorrect = $question->options->firstWhere('is_correct', 1)?->option_text === $answerData['answer_content'];
                if ($isCorrect) {
                    $correctCount++;
                }
            }

            // Lưu câu trả lời vào bảng Answer
            Answer::create([
                'answer_id' => Str::random(50),
                'submission_id' => $submission->submission_id,
                'question_title' => $question->title,
                'question_content' => $question->content,
                'question_answer' => $answerData['answer_content'],
            ]);
        }

        // Nếu là bài thi trắc nghiệm, tính điểm và cập nhật vào submission
        if ($examType === 'Trắc nghiệm' && $isExam) {
            $score = round(($correctCount / $total) * 10, 2);
            $submission->update(['temporary_score' => $score]);
        }

        if ($examType === 'Trắc nghiệm' && $isAssignment) {
            $score = round(($correctCount / $total) * 10, 2);
            $submission->update(['temporary_score' => $score]);
        }

        return response()->json([
            'message' => 'Nộp bài thành công!',
            'score' => $examType === 'Trắc nghiệm' ? $score : null, // Chỉ trả về điểm nếu là bài thi trắc nghiệm
            'correct' => $correctCount,
            'total' => $total
        ], 201);
    }




    /**
     * Get submission status for a student
     */
    public function getSubmissionStatus($student_id)
    {
        $submissions = Submission::where('student_id', $student_id)
            ->get();

        foreach ($submissions as $submission) {
            if ($submission->exam_id) {
                $submission->type = 'Exam';
                $submission->title = Exam::find($submission->exam_id)->title;
            } else if ($submission->assignment_id) {
                $submission->type = 'Assignment';
                $submission->title = Assignment::find($submission->assignment_id)->title;
            }
        }

        return response()->json($submissions);
    }

    /**
     * Get scores for a student
     */
    public function getScores($student_id)
    {
        $scores = Score::where('student_id', $student_id)->get();

        foreach ($scores as $score) {
            $score->course_name = $score->course->course_name;
        }

        return response()->json($scores);
    }

    /**
     * Get questions for an exam or assignment
     */
    public function getQuestions(Request $request)
    {
        $request->validate([
            'exam_id' => 'nullable|exists:exam,exam_id',
            'assignment_id' => 'nullable|exists:assignment,assignment_id',
        ]);

        if (
            (empty($request->exam_id) && empty($request->assignment_id)) ||
            (!empty($request->exam_id) && !empty($request->assignment_id))
        ) {
            return response()->json(['message' => 'Please provide either an exam_id or assignment_id.'], 400);
        }

        $subListId = null;

        if ($request->exam_id) {
            $subListId = Exam::find($request->exam_id)->sub_list_id;
        } else {
            $subListId = Assignment::find($request->assignment_id)->sub_list_id;
        }

        // Get questions from the sub_list
        $questions = Question::whereIn('question_id', function ($query) use ($subListId) {
            $query->select('question_id')
                ->from('sub_list_question')
                ->where('sub_list_id', $subListId);
        })->get();

        return response()->json($questions);
    }
    // Lấy danh sách tất cả submission của sinh viên theo assignment/exam
    public function listSubmissions(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:assignment,exam',
            'target_id' => 'required|uuid', // assignment_id hoặc exam_id
        ]);

        $query = Submission::query();

        if ($validated['type'] === 'assignment') {
            $query->where('assignment_id', $validated['target_id']);
            $assignment = Assignment::findOrFail($validated['target_id']);
            return view('submissions.index', [
                'submissions' => $query->with(['student'])->get(),
                'assignment' => $assignment
            ]);
        } else {
            $query->where('exam_id', $validated['target_id']);
            $exam = Exam::findOrFail($validated['target_id']);
            return view('submissions.index', [
                'submissions' => $query->with(['student'])->get(),
                'exam' => $exam
            ]);
        }
    }

    // Tạo mới submission
    public function storeSubmission(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|uuid|exists:students,id',
            'assignment_id' => 'nullable|uuid|exists:assignments,id',
            'exam_id' => 'nullable|uuid|exists:exams,id',
            'file_path' => 'nullable|string',
            'status' => 'nullable|in:submitted,draft,late',
        ]);

        // Bắt buộc phải có 1 trong 2: assignment_id hoặc exam_id
        if (!$validated['assignment_id'] && !$validated['exam_id']) {
            return response()->json(['error' => 'assignment_id hoặc exam_id là bắt buộc'], 422);
        }

        $submission = Submission::create([
            'id' => Str::uuid(),
            'student_id' => $validated['student_id'],
            'assignment_id' => $validated['assignment_id'] ?? null,
            'exam_id' => $validated['exam_id'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'status' => $validated['status'] ?? 'submitted',
        ]);

        return response()->json($submission, 201);
    }

    // Xem chi tiết submission
    public function showSubmission($id)
    {
        $submission = Submission::with(['student', 'assignment', 'exam'])->findOrFail($id);
        return response()->json($submission);
    }

    // Cập nhật submission
    public function updateSubmission(Request $request, $id)
    {
        $submission = Submission::findOrFail($id);

        $validated = $request->validate([
            'file_path' => 'nullable|string',
            'status' => 'nullable|in:submitted,draft,late',
        ]);

        $submission->update($validated);

        return response()->json($submission);
    }

    // Xoá submission
    public function deleteSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();

        return response()->json(['message' => 'Xoá thành công']);
    }
}
