@extends('layouts.dashboard')

@section('title', 'NIN Suspension Request')

@section('content')
    <div class="row">

        <div class="col-12 mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center my-3">
                        <h4 class="fw-bold mb-0">NIN Suspension Request</h4>
                        <img src="{{ asset('assets/images/img/nimc.png') }}"
                             alt="NIMC Logo"
                             height="60">
                    </div>

                    <div class="alert alert-info">
                        <h5 class="mb-1">
                            Processing Fee:
                            <span>₦{{ number_format($fee, 2) }}</span>
                        </h5>
                        <small>This covers the submission and processing of suspended NIN requests.</small>
                    </div>

                    <ul class="nav nav-tabs mb-3"
                        id="ninTabs"
                        role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active"
                                    id="form-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#formTab"
                                    type="button"
                                    role="tab">Submissions</button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                    id="history-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#historyTab"
                                    type="button"
                                    role="tab">Submission History</button>
                        </li>
                    </ul>

                    <div class="tab-content"
                         id="ninTabsContent">

                        <div class="tab-pane fade show active"
                             id="formTab"
                             role="tabpanel">

                            <form method="POST"
                                  action="{{ route('user.suspended-nin.store') }}"
                                  enctype="multipart/form-data">
                                @csrf

                                {{-- <h5 class="mt-3 mb-2">Personal Information <span class="text-danger">*</span></h5> --}}
                                <div class="row g-3">
                                    <!--                                     <div class="col-md-3">
                                                                    <label class="form-label">Title</label>
                                                                    <select name="title" class="form-select text-dark">
                                                                        <option value="">Select Title</option>
                                                                        <option value="Mr" {{ old('title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                                                        <option value="Mrs" {{ old('title') == 'Mrs' ? 'selected' : '' }}>Mrs
                                                                        </option>
                                                                        <option value="Miss" {{ old('title') == 'Miss' ? 'selected' : '' }}>Miss
                                                                        </option>
                                                                        <option value="Dr" {{ old('title') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                                                    </select>
                                                                </div> -->

                                    <div class="col-md-3">
                                        <label class="form-label">Enter your NIN number <span
                                                  class="text-danger">*</span></label>
                                        <input type="text"
                                               name="nin"
                                               maxlength="11"
                                               required
                                               class="form-control"
                                               value="{{ old('nin') }}"
                                               placeholder="Enter your nin number">
                                    </div>

                                    <!--                                     <div class="col-md-3">
                                                                    <label class="form-label">Surname <span class="text-danger">*</span></label>
                                                                    <input type="text" name="surname" required class="form-control"
                                                                        value="{{ old('surname') }}">
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                                                    <input type="text" name="first_name" required class="form-control"
                                                                        value="{{ old('first_name') }}">
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label">Middle Name</label>
                                                                    <input type="text" name="middle_name" class="form-control"
                                                                        value="{{ old('middle_name') }}">
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                                    <select name="gender" required class="form-select text-dark">
                                                                        <option value="">Select</option>
                                                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male
                                                                        </option>
                                                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female
                                                                        </option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                                    <input type="date" name="dob" required class="form-control"
                                                                        value="{{ old('dob') }}">
                                                                </div> -->
                                </div>

                                <!--                                 <h5 class="mb-2 mt-4">Residence Information <span class="text-danger">*</span></h5>
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Town/City of Residence</label>
                                                                    <input type="text" name="town_city" required class="form-control"
                                                                        value="{{ old('town_city') }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State of Residence</label>
                                                                    <input type="text" name="state_residence" required class="form-control"
                                                                        value="{{ old('state_residence') }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">LGA of Residence</label>
                                                                    <input type="text" name="lga_residence" required class="form-control"
                                                                        value="{{ old('lga_residence') }}">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Address of Residence</label>
                                                                    <textarea name="address_residence"
                                                                              required
                                                                              class="form-control"
                                                                              rows="2">{{ old('address_residence') }}</textarea>
                                                                </div>
                                                            </div> -->

                                <!--                                 <h5 class="mb-2 mt-4">Origin & Contact Information <span class="text-danger">*</span></h5>
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State of Origin</label>
                                                                    <input type="text" name="state_origin" required class="form-control"
                                                                        value="{{ old('state_origin') }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">LGA of Origin</label>
                                                                    <input type="text" name="lga_origin" required class="form-control"
                                                                        value="{{ old('lga_origin') }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Telephone Number</label>
                                                                    <input type="text" name="phone" required maxlength="11"
                                                                        class="form-control" value="{{ old('phone') }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Email Address</label>
                                                                    <input type="email" name="email" required class="form-control"
                                                                        value="{{ old('email') }}">
                                                                </div>
                                                            </div> -->

                                <!--                                 <h5 class="mb-2 mt-4">Passport Photograph <span class="text-danger">*</span></h5>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Upload Passport</label>
                                                                    <input type="file" name="photo" id="photoInput" accept="image/*"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label d-block">Preview</label>
                                                                    <img id="photoPreview" src="{{ asset('assets/images/img/default-avatar.jpg') }}"
                                                                        class="img-thumbnail" style="height:150px; width:150px; object-fit:cover;">
                                                                </div>
                                                            </div> -->

                                <div class="mt-4">
                                    <button type="submit"
                                            class="btn btn-primary px-5">Submit Request</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade"
                             id="historyTab"
                             role="tabpanel">
                            <h5 class="mb-3 mt-3">Submission History</h5>

                            @if ($records->count())
                                <div class="table-responsive">
                                    <table class="table-bordered table-hover table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>NIN</th>
                                                {{-- <th>Surname</th> --}}
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($records as $rec)
                                                <tr>
                                                    <td>{{ $rec->created_at->format('d M, Y') }}</td>
                                                    <td>{{ strtoupper($rec->refno) }}</td>
                                                    <td>{{ $rec->nin }}</td>
                                                    {{-- <td>{{ $rec->surname }}</td> --}}
                                                    <td>
                                                        @if ($rec->status == 'submitted')
                                                            <span class="badge bg-warning">Submitted</span>
                                                        @elseif($rec->status == 'processing')
                                                            <span class="badge bg-primary">Processing</span>
                                                        @elseif($rec->status == 'successful')
                                                            <span class="badge bg-success">Successful</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info text-white"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewModal{{ $rec->id }}">
                                                            View
                                                        </button>
                                                    </td>
                                                </tr>

                                                {{-- Modal --}}
                                                <div class="modal fade"
                                                     id="viewModal{{ $rec->id }}"
                                                     tabindex="-1"
                                                     aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable modal-md">
                                                        <div class="modal-content shadow-lg">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-file-text-fill me-2"></i> Request
                                                                    Details
                                                                </h5>
                                                                <button type="button"
                                                                        class="btn-close btn-close-white"
                                                                        data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                {{-- Personal Information --}}
                                                                <div class="bg-light mb-3 rounded border p-3">
                                                                    <h6 class="fw-bold text-dark">
                                                                        <i class="bi bi-person-fill me-1"></i> Submitted

                                                                    </h6>
                                                                    <div class="row">
                                                                        <!--                                                                         <div class="col-md-6"><strong>Title:</strong>
                                                                                            {{ $rec->title ?? '-' }}</div>
                                                                                        <div class="col-md-6"><strong>Surname:</strong>
                                                                                            {{ $rec->surname }}</div> -->
                                                                        <!--                                                                         <div class="col-md-6"><strong>First Name:</strong>
                                                                                            {{ $rec->first_name }}</div> -->
                                                                        <!--                                                                         <div class="col-md-6"><strong>Middle Name:</strong>
                                                                                            {{ $rec->middle_name ?? '-' }}</div> -->
                                                                        <div class="col-md-6"><strong>NIN:</strong>
                                                                            {{ $rec->nin }}</div>
                                                                        <!--                                                                         <div class="col-md-6"><strong>Gender:</strong>
                                                                                            {{ $rec->gender }}</div>
                                                                                        <div class="col-md-12"><strong>Date of
                                                                                                Birth:</strong> {{ $rec->dob }}</div>
                                                                                        <div class="col-md-6"><strong>Phone:</strong>
                                                                                            {{ $rec->phone }}</div>
                                                                                        <div class="col-md-6"><strong>Email:</strong>
                                                                                            {{ $rec->email }}</div> -->
                                                                    </div>
                                                                </div>

                                                                <!--                                                                 {{-- Passport Photo --}}
                                                                                <div class="bg-light mb-3 rounded border p-3">
                                                                                    <h6 class="fw-bold text-dark">
                                                                                        <i class="bi bi-image-fill me-1"></i> Passport
                                                                                        Photo
                                                                                    </h6>
                                                                                    <div class="text-center">
                                                                                        <img src="{{ asset('storage/' . $rec->photo) }}"
                                                                                             class="img-fluid rounded border"
                                                                                             style="max-height: 180px;">
                                                                                    </div>
                                                                                </div> -->

                                                                <!--                                                                 {{-- Residence & Origin --}}
                                                                                <div class="bg-light mb-3 rounded border p-3">
                                                                                    <h6 class="fw-bold text-dark">
                                                                                        <i class="bi bi-house-fill me-1"></i> Residence &
                                                                                        Origin
                                                                                    </h6>
                                                                                    <p class="mb-1"><strong>Town/City:</strong>
                                                                                        {{ $rec->town_city }}</p>
                                                                                    <p class="mb-1"><strong>State of Residence:</strong>
                                                                                        {{ $rec->state_residence }}</p>
                                                                                    <p class="mb-1"><strong>LGA of Residence:</strong>
                                                                                        {{ $rec->lga_residence }}</p>
                                                                                    <p class="mb-1"><strong>Address:</strong>
                                                                                        {{ $rec->address_residence }}</p>
                                                                                    <hr class="my-2">
                                                                                    <p class="mb-1"><strong>State of Origin:</strong>
                                                                                        {{ $rec->state_origin }}</p>
                                                                                    <p class="mb-1"><strong>LGA of Origin:</strong>
                                                                                        {{ $rec->lga_origin }}</p>
                                                                                </div> -->

                                                                {{-- Status & Comment --}}
                                                                <div class="bg-light rounded border p-3">
                                                                    <h6 class="fw-bold text-dark">
                                                                        <i class="bi bi-info-circle-fill me-1"></i> Status
                                                                        & Admin Comment
                                                                    </h6>
                                                                    <p class="mb-2">
                                                                        <strong>Status:</strong>
                                                                        @if ($rec->status == 'submitted')
                                                                            <span
                                                                                  class="badge bg-warning text-dark">Submitted</span>
                                                                        @elseif($rec->status == 'processing')
                                                                            <span
                                                                                  class="badge bg-primary">Processing</span>
                                                                        @elseif($rec->status == 'successful')
                                                                            <span
                                                                                  class="badge bg-success">Successful</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Rejected</span>
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-0"><strong>Comment:</strong></p>
                                                                    <div class="text-muted small mt-1">
                                                                        {!! $rec->reason ?? 'No comment yet.' !!}
                                                                    </div>
                                                                </div>
                                                                @if ($rec->result_file)
                                                                    <div
                                                                         class="border-success mb-1 mt-2 rounded border bg-white p-3">

                                                                        <a href="{{ asset($rec->result_file) }}"
                                                                           target="_blank"
                                                                           class="btn btn-sm btn-success w-100 fw-bold">DOWNLOAD
                                                                            RESULT</a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button"
                                                                        class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle me-1"></i> Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">{{ $records->links() }}</div>
                            @else
                                <p class="text-muted">No submissions found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('photoInput').addEventListener('change', function(e) {
            let file = e.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('photoPreview').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
