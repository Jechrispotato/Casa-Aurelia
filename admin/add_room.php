<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../includes/db.php');
include('../includes/paths.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    $price = (float) $_POST['price'];
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $uploaded_images = [];

    // Validate input
    if (empty($room_name) || $price <= 0) {
        $_SESSION['error'] = 'Invalid input: Room name and valid price are required.';
        header('Location: ' . ADMIN_PATH . 'add_room.php');
        exit;
    }

    // Handle multiple file uploads
    if (isset($_FILES['room_images']) && !empty($_FILES['room_images']['name'][0])) {
        $files = $_FILES['room_images'];
        $file_count = count($files['name']);
        
        // Validate file count (2-4 images)
        if ($file_count < 2 || $file_count > 4) {
            $_SESSION['error'] = 'Please upload between 2 and 4 images.';
            header('Location: ' . ADMIN_PATH . 'add_room.php');
            exit;
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Casa-Aurelia/images/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue; // Skip files with errors
            }

            $tmp_name = $files['tmp_name'][$i];
            $original_name = $files['name'][$i];
            $file_size = $files['size'][$i];

            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                $_SESSION['error'] = 'Invalid file type for "' . htmlspecialchars($original_name) . '". Only JPG, PNG, GIF, and WebP images are allowed.';
                header('Location: ' . ADMIN_PATH . 'add_room.php');
                exit;
            }

            // Validate file size
            if ($file_size > $max_size) {
                $_SESSION['error'] = 'File "' . htmlspecialchars($original_name) . '" is too large. Maximum size is 5MB.';
                header('Location: ' . ADMIN_PATH . 'add_room.php');
                exit;
            }

            // Generate a unique filename
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_filename = 'room_' . time() . '_' . uniqid() . '_' . $i . '.' . strtolower($extension);
            $upload_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $uploaded_images[] = $new_filename;
            } else {
                $_SESSION['error'] = 'Failed to upload "' . htmlspecialchars($original_name) . '". Please try again.';
                header('Location: ' . ADMIN_PATH . 'add_room.php');
                exit;
            }
        }
    }

    // Check if we have enough images
    if (count($uploaded_images) < 2) {
        $_SESSION['error'] = 'Please upload at least 2 images for the room.';
        header('Location: ' . ADMIN_PATH . 'add_room.php');
        exit;
    }

    // Store images as comma-separated string (first image is the main one)
    $room_image = mysqli_real_escape_string($conn, implode(',', $uploaded_images));

    // Insert new room
    $query = "INSERT INTO rooms (room_name, price, description, room_image) 
              VALUES ('$room_name', $price, '$description', '$room_image')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Room added successfully with ' . count($uploaded_images) . ' images.';
        header('Location: ' . ADMIN_PATH . 'rooms.php');
        exit;
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    // Render the Add Room page for GET requests
    include('../includes/header.php');
    include('sidebar.php');
    ?>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-2" style="font-family: 'AureliaLight';">Add New Room</h2>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="<?php echo ADMIN_PATH; ?>dashboard.php" class="hover:text-yellow-600 transition-colors">Dashboard</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="<?php echo ADMIN_PATH; ?>rooms.php" class="hover:text-yellow-600 transition-colors">Manage Rooms</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-white font-medium">Add Room</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-gray-900 rounded-2xl shadow-sm border border-gray-800 overflow-hidden">
            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-900/30 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                        <div class="flex gap-3">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            <p class="text-sm text-red-400"><?php echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo ADMIN_PATH; ?>add_room.php" enctype="multipart/form-data" id="addRoomForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="room_name" class="block text-sm font-bold text-gray-400 mb-2">Room Name</label>
                            <input type="text" name="room_name" id="room_name"
                                class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 outline-none transition-all placeholder-gray-600"
                                placeholder="e.g. Deluxe Suite" required>
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-400 mb-2">Price per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-gray-500">$</span>
                                <input type="number" name="price" id="price"
                                    class="w-full pl-8 pr-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 outline-none transition-all placeholder-gray-600"
                                    placeholder="0.00" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-400 mb-2">
                            Room Images <span class="text-yellow-600">(2-4 images required)</span>
                        </label>
                        
                        <!-- Hidden file input -->
                        <input type="file" name="room_images[]" id="room_images" accept="image/*" multiple
                            class="hidden">
                        
                        <!-- Upload Area -->
                        <div id="dropzone"
                            class="border-2 border-dashed border-gray-700 rounded-xl p-8 text-center hover:border-yellow-600 transition-all cursor-pointer bg-gray-800/50">
                            <div id="upload-placeholder" class="space-y-3">
                                <div class="w-16 h-16 mx-auto bg-gray-700 rounded-full flex items-center justify-center">
                                    <i class="fas fa-images text-2xl text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="text-gray-300 font-medium">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF or WebP (max 5MB each) • 2-4 images required</p>
                                </div>
                            </div>
                        </div>

                        <!-- Image Previews Grid -->
                        <div id="preview-grid" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 hidden">
                        </div>

                        <!-- Image Count Indicator -->
                        <div class="flex items-center justify-between mt-3">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Upload 2-4 images. The first image will be the main display image.
                            </p>
                            <span id="image-count" class="text-sm font-medium text-gray-400">0/4 images</span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="description" class="block text-sm font-bold text-gray-400 mb-2">Description</label>
                        <textarea name="description" id="description"
                            class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 outline-none transition-all placeholder-gray-600"
                            rows="6" placeholder="Describe the room details, amenities, and view..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-800">
                        <a href="<?php echo ADMIN_PATH; ?>rooms.php"
                            class="px-6 py-2.5 rounded-xl text-gray-400 font-bold hover:bg-gray-800 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn"
                            class="px-8 py-3 bg-yellow-600 text-white font-bold rounded-xl shadow-lg shadow-yellow-600/20 hover:bg-yellow-700 transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('room_images');
            const dropzone = document.getElementById('dropzone');
            const previewGrid = document.getElementById('preview-grid');
            const imageCount = document.getElementById('image-count');
            
            let selectedFiles = [];
            const MAX_FILES = 4;
            const MIN_FILES = 2;

            // Click on dropzone opens file picker
            dropzone.addEventListener('click', function(e) {
                fileInput.click();
            });

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                handleFiles(Array.from(this.files));
            });

            // Handle drag and drop
            dropzone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('border-yellow-600', 'bg-gray-700/50');
            });

            dropzone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('border-yellow-600', 'bg-gray-700/50');
            });

            dropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('border-yellow-600', 'bg-gray-700/50');
                handleFiles(Array.from(e.dataTransfer.files));
            });

            // Event delegation for remove buttons
            previewGrid.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-btn');
                if (removeBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const index = parseInt(removeBtn.dataset.index);
                    removeFile(index);
                }
                
                // Handle "Add More" button click
                const addMoreBtn = e.target.closest('.add-more-btn');
                if (addMoreBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.click();
                }
            });

            function handleFiles(files) {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                for (const file of files) {
                    // Check max files
                    if (selectedFiles.length >= MAX_FILES) {
                        alert('Maximum 4 images allowed.');
                        break;
                    }

                    // Validate file type
                    if (!validTypes.includes(file.type)) {
                        alert(`"${file.name}" is not a valid image file. Only JPG, PNG, GIF, or WebP allowed.`);
                        continue;
                    }

                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`"${file.name}" is too large. Maximum size is 5MB.`);
                        continue;
                    }

                    selectedFiles.push(file);
                }

                updatePreview();
                updateFileInput();
            }

            function updatePreview() {
                previewGrid.innerHTML = '';
                
                if (selectedFiles.length === 0) {
                    previewGrid.classList.add('hidden');
                    dropzone.classList.remove('hidden');
                    imageCount.textContent = '0/4 images';
                    imageCount.classList.remove('text-green-400', 'text-red-400');
                    imageCount.classList.add('text-gray-400');
                    return;
                }

                previewGrid.classList.remove('hidden');
                dropzone.classList.add('hidden');
                
                // Create image cards
                selectedFiles.forEach((file, index) => {
                    const card = document.createElement('div');
                    card.className = 'relative rounded-xl overflow-hidden bg-gray-800 border border-gray-700';
                    card.dataset.index = index;
                    
                    // Create image element
                    const img = document.createElement('img');
                    img.className = 'w-full h-32 object-cover';
                    img.alt = `Preview ${index + 1}`;
                    
                    // Read and display image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    card.appendChild(img);
                    
                    // Remove button (always visible)
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-btn absolute top-2 right-2 w-7 h-7 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors flex items-center justify-center shadow-lg';
                    removeBtn.dataset.index = index;
                    removeBtn.innerHTML = '<i class="fas fa-times text-sm"></i>';
                    card.appendChild(removeBtn);
                    
                    // Main badge for first image
                    if (index === 0) {
                        const badge = document.createElement('div');
                        badge.className = 'absolute top-2 left-2 bg-yellow-600 text-white text-xs px-2 py-1 rounded-full font-bold';
                        badge.textContent = 'Main';
                        card.appendChild(badge);
                    }
                    
                    // Filename
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'p-2';
                    nameDiv.innerHTML = `<p class="text-xs text-gray-400 truncate">${file.name}</p>`;
                    card.appendChild(nameDiv);
                    
                    previewGrid.appendChild(card);
                });

                // Add "Add More" card if under max
                if (selectedFiles.length < MAX_FILES) {
                    const addCard = document.createElement('div');
                    addCard.className = 'add-more-btn rounded-xl border-2 border-dashed border-gray-700 bg-gray-800/50 hover:border-yellow-600 hover:bg-gray-700/50 transition-all cursor-pointer flex flex-col items-center justify-center h-[168px]';
                    addCard.innerHTML = `
                        <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-plus text-gray-400"></i>
                        </div>
                        <p class="text-xs text-gray-500">Add More</p>
                        <p class="text-xs text-gray-600">${MAX_FILES - selectedFiles.length} remaining</p>
                    `;
                    previewGrid.appendChild(addCard);
                }

                // Update count
                imageCount.textContent = `${selectedFiles.length}/4 images`;
                if (selectedFiles.length >= MIN_FILES) {
                    imageCount.classList.remove('text-gray-400', 'text-red-400');
                    imageCount.classList.add('text-green-400');
                } else {
                    imageCount.classList.remove('text-gray-400', 'text-green-400');
                    imageCount.classList.add('text-red-400');
                }
            }

            function removeFile(index) {
                selectedFiles.splice(index, 1);
                updatePreview();
                updateFileInput();
            }

            function updateFileInput() {
                // Create a new DataTransfer to update the file input
                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
            }

            // Form validation before submit
            document.getElementById('addRoomForm').addEventListener('submit', function(e) {
                if (selectedFiles.length < MIN_FILES) {
                    e.preventDefault();
                    alert('Please upload at least 2 images for the room.');
                    return false;
                }
                if (selectedFiles.length > MAX_FILES) {
                    e.preventDefault();
                    alert('Maximum 4 images allowed.');
                    return false;
                }
            });
        });
    </script>

    </main>
    </div>
    </div>

    <?php include('../includes/footer.php');
    exit;
}
?>