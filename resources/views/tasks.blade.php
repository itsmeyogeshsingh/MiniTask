<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light py-4">

<div class="container">
    <div class="row">
        <!-- Task List Section -->
        <div class="col-md-8">
            <h2 class="mb-4 text-center text-primary">Mini Task Manager</h2>
            <div class="card">
                <div class="card-header bg-secondary text-white">Task List</div>
                <div class="card-body">
                    <!-- Search and Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="searchTask" class="form-label">Search Task</label>
                            <input type="text" id="searchTask" name="searchTask" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="priorityFilter" class="form-label">Priority Filter</label>
                            <select id="priorityFilter" name="priorityFilter" class="form-select">
                                <option value="">Select Priority</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <!-- Task Table -->
                    <table class="table table-bordered table-striped" id="taskTable">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tasksList">
                            <!-- Tasks will be loaded here -->
                        </tbody>
                    </table>
                    <div id="pagination" class="pagination-container"></div>

                </div>
            </div>
        </div>
        <!-- Task Form Section -->
        <div class="col-md-4" style="margin-top: 4em">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Add New Task</div>
                <div class="card-body">
                    <form id="taskForm">
                        <input type="hidden" name="task_id" id="task_id">

                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" name="title" id="title" class="form-control">
                                <div class="invalid-feedback" id="title-error"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority *</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="">Select Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                                <div class="invalid-feedback" id="priority-error"></div>
                            </div>
                            @php
                                $today = date('Y-m-d');
                            @endphp
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date *</label>
                                <input type="date" name="due_date" class="form-control" min="{{ $today }}">
                                <div class="invalid-feedback" id="due_date-error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100"  id="submitBtn">Create Task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to load tasks
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function loadTasks(page = 1) {
        const searchTerm = $('#searchTask').val();
        const priority = $('#priorityFilter').val();

        $.ajax({
            type: "GET",
            url: "{{ route('tasks') }}",
            data: {
                searchTask: searchTerm,
                priorityFilter: priority,
                page: page
            },
            success: function (response) {
                let rows = '';
                if (response.data.length === 0) {
                    rows = `<tr><td colspan="5" class="text-center text-muted">No tasks found.</td></tr>`;
                } else {
                    response.data.forEach(task => {
                        rows += `
                            <tr>
                                <td>${task.title}</td>
                                <td>${task.priority}</td>
                                <td>${task.due_date}</td>
                                <td>${task.status}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1" onclick="editTask(${task.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">Delete</button>
                                    <button class="btn btn-sm btn-info" onclick="viewTask(${task.id})">View</button>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#taskTable tbody').html(rows);

                let pagination = '';
                for (let i = 1; i <= response.last_page; i++) {
                    pagination += `<button class="btn btn-sm btn-link ${i === response.current_page ? 'active' : ''}" onclick="loadTasks(${i})">${i}</button>`;
                }

                $('#pagination').html(pagination);

            },
            error: function (err) {
                toastr.error('Failed to load tasks.');
            }
        });
    }

    // Function to view a task (disabled fields)
    function viewTask(id) {
        const getUrl = "{{ route('editTask', ':id') }}".replace(':id', id);

        $.ajax({
            type: "GET",
            url: getUrl,
            success: function (response) {
                $('[name="task_id"]').val('');
                $('[name="title"]').val(response.title).prop('disabled', true);
                $('[name="priority"]').val(response.priority).prop('disabled', true);
                $('[name="due_date"]').val(response.due_date).prop('disabled', true);
                $('[name="description"]').val(response.description).prop('disabled', true);
                $('#submitBtn').hide();
            },
            error: function (err) {
                console.error(err.responseText);
            }
        });
    }

    // Function to edit a task (enable fields)
    function editTask(id) {
        const getUrl = "{{ route('editTask', ':id') }}".replace(':id', id);

        $.ajax({
            type: "GET",
            url: getUrl,
            success: function (response) {
                // Fill form fields with fetched data
                $('[name="task_id"]').val(response.id);
                $('[name="title"]').val(response.title).prop('disabled', false);
                $('[name="priority"]').val(response.priority).prop('disabled', false);
                $('[name="due_date"]').val(response.due_date).prop('disabled', false);
                $('[name="description"]').val(response.description).prop('disabled', false);

                // Show the update button
                $('#submitBtn').text('Update Task').show();
                // toastr.info('Task details loaded for editing.');
            },
            error: function (err) {
                console.error(err.responseText);
            }
        });
    }

    // Function to update task status
    function updateTaskStatus(id, status) {
        const getUrl = "{{ route('updateStatus', ':id') }}".replace(':id', id);

        $.ajax({
            type: "PUT",
            url: getUrl,
            data: {
                status: status,
                _token: token
            },
            success: function (response) {
                console.log('Task status updated successfully!');
                toastr.success('Task status updated successfully!');
            },
            error: function (err) {
                console.error(err.responseText);
            }
        });
    }

    // Function to delete a task
    function deleteTask(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                const getUrl = "{{ route('delete', ':id') }}".replace(':id', id);

                $.ajax({
                    type: "DELETE",
                    url: getUrl,
                    data: {
                        _token: token
                    },
                    success: function () {
                        Swal.fire("Deleted!", "Task has been deleted.", "success");
                        loadTasks();
                    },
                    error: function (xhr) {
                        console.log('Error:', xhr.responseText);
                    }
                });
            }
        });
    }


    // Helper function to enable or disable form fields
    function setFormDisabled(state) {
        $('[name="title"]').prop('disabled', state);
        $('[name="priority"]').prop('disabled', state);
        $('[name="due_date"]').prop('disabled', state);
        $('[name="description"]').prop('disabled', state);
    }

    // Submit form for create or update task
    $("#taskForm").submit(function (e) {
        e.preventDefault();

        // Clear any previous errors
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');

        var formData = new FormData(this);
        let taskId = $('[name="task_id"]').val();

        let url = taskId
            ? "{{ route('updateTask', ':id') }}".replace(':id', taskId) // Update
            : "{{ route('store') }}"; // Create

        if (taskId) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $("#taskForm")[0].reset();
                $('[name="task_id"]').val('');
                $('#submitBtn').text('Create Task').show();
                setFormDisabled(false);
                loadTasks();
                // toastr.success(response.message);
                toastr.success(taskId ? 'Task updated successfully!' : 'Task created successfully!');
            },
            error: function (err) {
                var errors = err.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    $('#' + field).addClass('is-invalid');
                    $('#' + field + '-error').text(messages[0]);
                });
            }
        });
    });

    $('.form-control, .form-select').on('input change', function() {
        var field = $(this).attr('name');
        $('#' + field).removeClass('is-invalid');
        $('#' + field + '-error').text('');
    });

    $(document).ready(function () {
        loadTasks();

        $('#searchTask, #priorityFilter').on('input change', function() {
            loadTasks();
        });
    });

</script>

</body>
</html>
