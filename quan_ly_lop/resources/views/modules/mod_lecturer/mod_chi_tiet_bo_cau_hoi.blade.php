@extends('templates.template_lecture')
@section('main-content')
    <div class="container mt-4">
        <!-- Modal Tạo Mã Đề -->
        <div class="modal fade" id="createSublistModal" tabindex="-1" aria-labelledby="createSublistModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="createSublistForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createSublistModalLabel">Tạo mã đề</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Tên mã đề</label>
                                <input type="text" class="form-control" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="number_of_questions" class="form-label">Số lượng câu hỏi</label>
                                <input type="number" class="form-control" id="number_of_questions" required min="1">
                            </div>
                            <div class="mb-3">
                                <label for="question_type" class="form-label">Loại câu hỏi</label>
                                <select id="question_type">
                                    <option value="">Tất cả</option>
                                    <option value="multiple_choice">Trắc nghiệm</option>
                                    <option value="short_answer">Tự luận</option>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="isShuffle" checked>
                                <label class="form-check-label" for="isShuffle">
                                    Trộn thứ tự câu hỏi
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Tạo mã đề</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-4" onclick="window.history.back()">Quay lại</button>
        <h2 class="mb-4 text-primary fw-bold">📋 Chi tiết câu hỏi</h2>
        <div class="tab-pane fade" id="sublistTab" role="tabpanel" aria-labelledby="sublistTabLabel">
            <h4 class="mb-4 ">Danh sách đề được tạo ra của bộ câu hỏi</h4>
            <div id="sublistContainer" class="row g-3">
                <!-- Cards sẽ được inject ở đây -->
            </div>
        </div>
        <div class="mb-2">
            <button type="button createSubmit" class="btn btn-primary mb-4" onclick="createSublist()">Tạo mã đề</button>
        </div>
        <div class="card custom-card shadow-lg border-0 mb-4 rounded-4 bg-dark-subtle">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-black rounded-circle d-flex justify-content-center align-items-center"
                        style="width: 60px; height: 60px;">
                        <i class="bi bi-journal-text fs-2"></i> <!-- Bootstrap Icon -->
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Bộ câu hỏi của giảng viên</h5>
                        <p class="mb-0"><strong class="text-dark">Tên môn học:</strong> <span id="course-name"
                                class="text-muted">Đang tải...</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion" id="questionList">
        </div>
        <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="editQuestionForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editQuestionModalLabel">Sửa câu hỏi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-question-id">

                            <div class="mb-3">
                                <label for="edit-title" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="edit-title" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-content" class="form-label">Nội dung</label>
                                <textarea class="form-control" id="edit-content" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="edit-type" class="form-label">Loại câu hỏi</label>
                                <select class="form-select" id="edit-type">
                                    <option value="multiple_choice">Trắc nghiệm</option>
                                    <option value="short_answer">Tự luận</option>
                                </select>
                            </div>

                            <!-- Phần đáp án -->
                            <div class="mb-3">
                                <label class="form-label">Đáp án</label>
                                <div id="edit-options-group">
                                    <input type="text" class="form-control mb-2" id="edit-option-A" placeholder="Đáp án A">
                                    <input type="text" class="form-control mb-2" id="edit-option-B" placeholder="Đáp án B">
                                    <input type="text" class="form-control mb-2" id="edit-option-C" placeholder="Đáp án C">
                                    <input type="text" class="form-control mb-2" id="edit-option-D" placeholder="Đáp án D">
                                </div>
                            </div>

                            <!-- Chọn đáp án đúng -->
                            <div class="mb-3">
                                <label for="edit-correct-answer" class="form-label">Đáp án đúng</label>
                                <select class="form-select" id="edit-correct-answer" required>
                                    <option value="">-- Chọn --</option>
                                    <option value="A">Đáp án A</option>
                                    <option value="B">Đáp án B</option>
                                    <option value="C">Đáp án C</option>
                                    <option value="D">Đáp án D</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let listQuestionId = null;
        document.addEventListener("DOMContentLoaded", function () {
            listQuestionId = "{{ $list_question_id }}";
            localStorage.setItem('current_list_question_id', listQuestionId);
            fetchSubLists(listQuestionId);
            fetch(`http://127.0.0.1:8000/api/list-questions/detail/${listQuestionId}`)
                .then(response => response.json())
                .then(result => {
                    const questionList = document.getElementById("questionList");

                    if (!result.data) {
                        questionList.innerHTML = `
                        <div class="alert alert-warning">Không tìm thấy danh sách câu hỏi!</div>`;
                        return;
                    }

                    const { course_id, course_name, questions } = result.data;
                    document.getElementById("course-name").textContent = course_name;

                    if (!questions || questions.length === 0) {
                        questionList.innerHTML = `
                                                                        <div class="alert alert-warning">Chưa có câu hỏi nào.</div>`;
                        return;
                    }

                    questions.forEach((question, index) => {
                        const html = `
                                                                            <div class="accordion-item mb-2" id="question-${question.question_id}">
                                                                                <h2 class="accordion-header" id="heading${index}">
                                                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                                        data-bs-target="#collapse${index}">
                                                                                        Câu hỏi: ${question.title}
                                                                                    </button>
                                                                                </h2>
                                                                                <div id="collapse${index}" class="accordion-collapse collapse" data-bs-parent="#questionList">
                                                                                    <div class="accordion-body">
                                                                                        <p><strong>Nội dung:</strong> ${question.content}</p>
                                                                                        <p><strong>Loại:</strong> ${question.type}</p>
                                                                                        <p><strong>Đáp án đúng:</strong> ${question.correct_answer ?? 'Không có'}</p>
                                                                                        ${renderOptions(question.options)}
                                                                                        <div class="d-flex gap-2 mt-3">
                                                                                            <button class="btn btn-warning btn-sm edit-button" data-question-id="${question.question_id}">Sửa</button>
                                                                                            <button class="btn btn-danger btn-sm" onclick="deleteQuestion('${question.question_id}')">Xóa bỏ</button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        `;
                        questionList.insertAdjacentHTML('beforeend', html);
                    });

                    // Gán sự kiện cho tất cả các nút "Sửa"
                    const editButtons = document.querySelectorAll('.edit-button');
                    editButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            const questionId = button.getAttribute('data-question-id');
                            editQuestion(questionId);
                        });
                    });

                })
                .catch(error => {
                    console.error('Lỗi khi fetch dữ liệu:', error);
                    document.getElementById("questionList").innerHTML = `
                                                                    <div class="alert alert-danger">Lỗi khi tải dữ liệu.</div>`;
                });

            function renderOptions(options) {
                if (!options || options.length === 0) return '';
                return `
                                                                <p><strong>Các lựa chọn:</strong></p>
                                                                <ul>
                                                                    <li>${options[0]}</li>
                                                                    <li>${options[1]}</li>
                                                                    <li>${options[2]}</li>
                                                                    <li>${options[3]}</li>
                                                                </ul>
                                                            `;
            }

            function editQuestion(questionId) {
                // Lấy dữ liệu câu hỏi từ DOM
                const card = document.getElementById(`question-${questionId}`);
                const title = card.querySelector('.accordion-button').textContent.replace("Câu hỏi: ", "").trim();
                const content = card.querySelector('p:nth-of-type(1)').textContent.replace("Nội dung:", "").trim();
                const type = card.querySelector('p:nth-of-type(2)').textContent.replace("Loại:", "").trim();
                const correctAnswer = card.querySelector('p:nth-of-type(3)').textContent.replace("Đáp án đúng:", "").trim();
                const options = card.querySelectorAll('.accordion-body ul li'); // Lấy các đáp án (nếu có)

                // Điền vào form trong modal
                document.getElementById('edit-question-id').value = questionId;
                document.getElementById('edit-title').value = title;
                document.getElementById('edit-content').value = content;
                document.getElementById('edit-type').value = type;
                document.getElementById('edit-correct-answer').value = correctAnswer;

                // Kiểm tra loại câu hỏi và ẩn/hiện các ô đáp án trắc nghiệm
                if (type === "Tự luận") {
                    // Nếu là câu hỏi tự luận, ẩn phần đáp án trắc nghiệm
                    document.getElementById('edit-options-group').style.display = 'none';
                } else {
                    // Nếu là câu hỏi trắc nghiệm, hiển thị phần đáp án trắc nghiệm
                    document.getElementById('edit-options-group').style.display = 'block';

                    // Điền đáp án vào các ô
                    options.forEach((option, index) => {
                        const optionValue = option.textContent.trim();
                        document.getElementById(`edit-option-${String.fromCharCode(65 + index)}`).value = optionValue;
                    });
                }

                // Mở modal
                new bootstrap.Modal(document.getElementById('editQuestionModal')).show();
            }
            document.getElementById('editQuestionForm').addEventListener('submit', function (e) {
                const questionId = document.getElementById('edit-question-id').value;
                const data = {
                    title: document.getElementById('edit-title').value,
                    content: document.getElementById('edit-content').value,
                    type: document.getElementById('edit-type').value,
                    correct_answer: document.getElementById('edit-correct-answer').value,
                };
                fetch(`http://127.0.0.1:8000/api/questions/update/${questionId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Cập nhật thất bại');
                        return response.json();
                    })
                    .then(result => {
                        alert("Cập nhật thành công!");
                        location.reload();
                    })
                    .catch(error => {
                        console.error(error);
                        alert("Có lỗi khi cập nhật câu hỏi!");
                    });
            });
            function deleteQuestion(questionId) {
                if (!confirm("Bạn có chắc chắn muốn xoá câu hỏi này không?")) return;

                fetch(`http://127.0.0.1:8000/api/questions/${questionId}`, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error("Lỗi khi xoá câu hỏi.");
                        document.getElementById(`question-${questionId}`).remove();
                    })
                    .catch(error => {
                        console.error(error);
                        alert("Xoá thất bại. Vui lòng thử lại!");
                    });
            }
        });
        // Lấy dữ liệu từ form
        const questionId = document.getElementById('edit-question-id').value;
        const data = {
            title: document.getElementById('edit-title').value,
            content: document.getElementById('edit-content').value,
            type: document.getElementById('edit-type').value,
            correct_answer: document.getElementById('edit-correct-answer').value,
        };
        document.getElementById('editQuestionForm').addEventListener('submit', function (e) {
            // Lấy dữ liệu từ form
            const questionId = document.getElementById('edit-question-id').value;
            const data = {
                title: document.getElementById('edit-title').value,
                content: document.getElementById('edit-content').value,
                type: document.getElementById('edit-type').value,
                correct_answer: document.getElementById('edit-correct-answer').value,
            };

            console.log(data);  // In ra dữ liệu để kiểm tra

            // Gửi yêu cầu PUT tới API để cập nhật câu hỏi
            fetch(`http://127.0.0.1:8000/api/questions/update/${questionId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
                .then(response => {
                    if (!response.ok) throw new Error('Cập nhật thất bại');
                    return response.json();
                })
                .then(result => {
                    alert("Cập nhật thành công!");  // Thông báo thành công
                    location.reload();  // Reload trang để cập nhật thay đổi
                })
                .catch(error => {
                    console.error(error);
                    alert("Có lỗi khi cập nhật câu hỏi!");  // Thông báo lỗi
                });
        });

        function createSublist() {
            const modal = new bootstrap.Modal(document.getElementById('createSublistModal'));
            modal.show();
        }

        document.getElementById('createSublistForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const number_of_questions = parseInt(document.getElementById('number_of_questions').value);
            const question_type = document.getElementById('question_type').value || null;
            if (question_type === "") question_type = null;
            const isShuffle = document.getElementById('isShuffle').checked;
            const list_question_id = listQuestionId;
            console.log(list_question_id);
            if (!list_question_id) {
                alert("Không tìm thấy ID bộ đề tổng.");
                return;
            }

            try {
                const res = await fetch('http://localhost:8000/api/sub-lists/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title,
                        number_of_questions,
                        question_type,
                        isShuffle,
                        list_question_id,
                    })
                });

                const data = await res.json();
                if (res.ok) {
                    alert("✅ Tạo mã đề thành công!");
                    document.getElementById('createSublistForm').reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createSublistModal'));
                    modal.hide();
                    // reload lại danh sách sublist nếu cần
                } else {
                    console.error(data);
                    alert("❌ Lỗi tạo mã đề: " + (data.message || 'Có lỗi xảy ra.'));
                }

            } catch (err) {
                console.error(err);
                alert("❌ Lỗi kết nối đến server.");
            }
        });

        function fetchSubLists(listQuestionId) {
            console.log(`http://127.0.0.1:8000/api/sub-lists/getAll/${listQuestionId}`);

            fetch(`http://127.0.0.1:8000/api/sub-lists/getAll/${listQuestionId}`)
                .then(response => {
                    if (response.headers.get('Content-Type')?.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error("Received HTML instead of JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    const sublistContainer = document.getElementById('sublistContainer');
                    sublistContainer.innerHTML = '';

                    if (data.message) {
                        sublistContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                        return;
                    }
                    console.log('Data nhận được từ API:', data);

                    data.forEach(sublist => {
                        const col = document.createElement('div');
                        col.className = 'col-md-4 col-sm-6';

                        const card = document.createElement('div');
                        card.className = 'card h-100 shadow-sm border-0 sublist-card transition-hover';
                        card.style.cursor = 'pointer';

                        card.innerHTML = `
                                    <div class="card-body">
                                        <h5 class="card-title">${sublist.title}</h5>
                                        <p class="card-text mb-1"><strong>ID:</strong> ${sublist.sub_list_id}</p>
                                        <p class="card-text"><strong>Trộn câu:</strong> ${sublist.is_shuffle ? 'Có' : 'Không'}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-end">
                                        <small class="text-muted">Tạo lúc: ${new Date(sublist.created_at).toLocaleString()}</small>
                                    </div>
                                `;

                        col.appendChild(card);
                        sublistContainer.appendChild(col);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    const sublistContainer = document.getElementById('sublistContainer');
                    sublistContainer.innerHTML = `<div class="alert alert-danger">Lỗi khi tải dữ liệu: ${error.message}</div>`;
                });
        }

    </script>
    <style>
        .sublist-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sublist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .custom-card {
            background-color: #343a40;
            /* Màu nền tối */
            color: #f8f9fa;
            /* Màu chữ sáng */
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .custom-card:hover {
            transform: translateY(-5px);
            /* Hiệu ứng nâng lên khi hover */
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            /* Bóng đổ sâu hơn */
        }

        .custom-card .card-body {
            padding: 20px;
        }

        .custom-card .card-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .custom-card .text-muted {
            color: rgb(21, 25, 29);
            /* Màu chữ nhạt cho các thông tin phụ */
        }

        .custom-card .bg-primary {
            background-color: #6c757d !important;
            /* Màu nền icon tròn */
        }

        .custom-card i {
            font-size: 2rem;
            /* Kích thước icon */
        }

        .custom-card .card-body p {
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Hiệu ứng cho tiêu đề khi hover */
        .custom-card .card-title:hover {
            color: #ff7e5f;
            /* Màu chữ khi hover */
        }
    </style>
@endsection
