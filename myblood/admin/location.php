<?php
include('../db_connect.php');
include('admin_header.php');
include('sidebar.php');

// Base query for locations
$query = "SELECT * FROM location ORDER BY loc_id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease-in-out;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }
        .location-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-buttons .btn {
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Locations</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>State</th>
                            <th>Address</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Rating</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['loc_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['loc_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['loc_state']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo $row['latitude']; ?></td>
                                <td><?php echo $row['longitude']; ?></td>
                                <td><?php echo $row['rating']; ?></td>
                                <td>
                                    <img src="../assets/images/<?php echo $row['image']; ?>" 
                                         alt="Location Image" 
                                         class="location-image">
                                </td>
                                <td class="action-buttons">
                                <button class="btn btn-primary btn-sm edit-btn" 
                                            data-id="<?php echo $row['loc_id']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editLocationModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" 
                                            data-id="<?php echo $row['loc_id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Location Modal -->
    <div class="modal fade" id="addLocationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addLocationForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Location Name</label>
                            <input type="text" class="form-control" name="loc_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="loc_state" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" name="latitude" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" name="longitude" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <input type="number" step="0.1" min="0" max="5" class="form-control" name="rating" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Location</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editLocationForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="loc_id" id="edit_loc_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" class="form-control" name="loc_name" id="edit_loc_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" name="loc_state" id="edit_loc_state" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" name="latitude" id="edit_latitude" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" name="longitude" id="edit_longitude" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <input type="number" step="0.1" min="0" max="5" class="form-control" name="rating" id="edit_rating" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sidebar Toggle
    document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            document.getElementById('toggleSidebar').addEventListener('click', function() {
                mainContent.classList.toggle('expanded');
            });
        });

        $(document).ready(function() {
    // Add Location Form Submission
    $('#addLocationForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        $.ajax({
            url: 'location_save.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    alert(res.message);
                    if(res.success) {
                        $('#addLocationModal').modal('hide');
                        location.reload();
                    }
                } catch(e) {
                    alert('Error processing response');
                }
            },
            error: function() {
                alert('Error adding location');
            }
        });
    });

    // Edit Location Button Click
    $(document).on('click', '.edit-btn', function() {
        var loc_id = $(this).data('id');
        $.ajax({
            url: 'fetch_location.php',
            type: 'POST',
            data: {loc_id: loc_id},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#edit_loc_id').val(response.data.loc_id);
                    $('#edit_loc_name').val(response.data.loc_name);
                    $('#edit_loc_state').val(response.data.loc_state);
                    $('#edit_address').val(response.data.address);
                    $('#edit_latitude').val(response.data.latitude);
                    $('#edit_longitude').val(response.data.longitude);
                    $('#edit_rating').val(response.data.rating);
                    $('#editLocationModal').modal('show');
                } else {
                    alert('Error fetching location data: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching location data');
            }
        });
    });

    // Edit Location Form Submission
    $('#editLocationForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('update_location', true);
        
        $.ajax({
            url: 'location_edit.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const res = typeof response === 'string' ? JSON.parse(response) : response;
                    alert(res.message);
                    if(res.success) {
                        $('#editLocationModal').modal('hide');
                        location.reload();
                    }
                } catch(e) {
                    alert('Error processing response');
                }
            },
            error: function() {
                alert('Error updating location');
            }
        });
    });

    // Delete Location Button Click
    $('.delete-btn').on('click', function() {
        if(confirm('Are you sure you want to delete this location?')) {
            let id = $(this).data('id');
            $.ajax({
                url: 'location_delete.php',
                type: 'POST',
                data: {id: id},
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if(response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error deleting location');
                }
            });
        }
    });
});

    </script>
</body>
</html>
