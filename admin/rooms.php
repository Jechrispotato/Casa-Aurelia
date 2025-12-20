<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . AUTH_PATH . 'login.php');
    exit;
}

include('../includes/header.php');
include('sidebar.php');
include('../includes/db.php');

// Get all rooms with their booking status
$rooms_query = "SELECT r.*, 
                (SELECT COUNT(*) FROM bookings b 
                WHERE b.room_id = r.id 
                AND b.status IN ('approved', 'pending')
                AND NOW() >= b.check_in_date 
                AND NOW() < DATE_ADD(b.check_out_date, INTERVAL 20 MINUTE)
                ) as is_booked,
                (SELECT DATE_ADD(MAX(b.check_out_date), INTERVAL 20 MINUTE) FROM bookings b 
                WHERE b.room_id = r.id 
                AND b.status IN ('approved', 'pending')
                AND NOW() >= b.check_in_date 
                AND NOW() < DATE_ADD(b.check_out_date, INTERVAL 20 MINUTE)
                ) as next_available_date
                FROM rooms r
                ORDER BY room_name ASC";
$rooms = mysqli_query($conn, $rooms_query);

// Store rooms data for JavaScript
$rooms_data = [];
if ($rooms) {
    while ($room = mysqli_fetch_assoc($rooms)) {
        // Ensure hourly price is available (fallback if 0)
        $room['hourly_price'] = (isset($room['price_per_hour']) && $room['price_per_hour'] > 0)
            ? $room['price_per_hour']
            : ceil($room['price'] * 0.15);
        $rooms_data[] = $room;
    }
    // Reset pointer for the HTML loop
    mysqli_data_seek($rooms, 0);
}

// Function to format price
function formatPrice($price)
{
    return number_format($price, 2);
}
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-white mb-2" style="font-family: 'AureliaLight';">Manage Rooms</h2>
    <p class="text-gray-400">Update and Maintenance of Rooms.</p>
</div>

<!-- Rooms Grid -->
<?php if ($rooms && mysqli_num_rows($rooms) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php while ($room = mysqli_fetch_assoc($rooms)): ?>
            <div class="room_card group bg-gray-800/50 backdrop-blur-sm rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 flex flex-col h-full transform hover:-translate-y-2 cursor-pointer room-clickable"
                data-room-id="<?php echo $room['id']; ?>">

                <!-- Image Area -->
                <div class="relative h-72 bg-gray-200 overflow-hidden">
                    <!-- Status Badge -->
                    <?php if ($room['is_booked'] > 0): ?>
                        <div
                            class="availabity_unavailable absolute top-4 right-4 z-20 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                            Unavailable
                        </div>
                    <?php else: ?>
                        <div
                            class="availabity_available absolute top-4 right-4 z-20 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                            Available
                        </div>
                    <?php endif; ?>

                    <?php
                    // Parse room images - handle both comma-separated and single image
                    $room_images = [];
                    $use_legacy_pattern = false;

                    if (!empty($room['room_image'])) {
                        if (strpos($room['room_image'], ',') !== false) {
                            // Multiple images stored as comma-separated (new format)
                            $room_images = array_filter(array_map('trim', explode(',', $room['room_image'])));
                        } else {
                            // Single image (legacy format) - need to find additional images
                            $room_images[] = $room['room_image'];
                            $use_legacy_pattern = true;
                        }
                    }

                    // For legacy single images, find additional images using pattern matching
                    if ($use_legacy_pattern && count($room_images) === 1) {
                        $roomType = strtolower(preg_replace('/\s+/', '', $room['room_name']));
                        $searchPatterns = [
                            'penthousesuite' => 'penthouse',
                            'bridalsuite' => 'bridalsuite',
                            'honeymoon' => 'honeymoon',
                            'honeymoonroom' => 'honeymoon',
                            'honeymoonsuite' => 'honeymoon',
                            'king' => 'kings',
                            'kingsbed' => 'kings',
                            'kingbed' => 'kings',
                            'queen' => 'queen',
                            'queensbed' => 'queen',
                            'queenbed' => 'queen',
                            'singleroom' => 'singleroom',
                            'doubleroom' => 'doubleroom',
                            'suiteroom' => 'suiteroom',
                            'seasideview' => 'seaside'
                        ];
                        $normalizedRoomName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $room['room_name']));

                        $searchPattern = $roomType;
                        if (isset($searchPatterns[$roomType])) {
                            $searchPattern = $searchPatterns[$roomType];
                        } elseif (isset($searchPatterns[$normalizedRoomName])) {
                            $searchPattern = $searchPatterns[$normalizedRoomName];
                        } else {
                            foreach ($searchPatterns as $key => $pattern) {
                                if (strpos($normalizedRoomName, $key) !== false) {
                                    $searchPattern = $pattern;
                                    break;
                                }
                            }
                        }

                        $roomAdditionalImages = [
                            'honeymoon' => ['honeymoonbathroom.jpg', 'honeymoonkitchen.jpg', 'honeymoonlivingarea.jpg'],
                            'penthouse' => ['penthousebathroom.jpg', 'penthousebedroom.jpg', 'penthouse kitchen.jpg'],
                            'bridalsuite' => ['bridalsuitebathroom.jpg', 'bridalsuitediningroom.jpg', 'bridalsuitebredroom.jpg'],
                            'kings' => ['kingsbathroom.jpg', 'kingskitchen.jpg'],
                            'queen' => ['queenbalcony.jpg', 'queenkitchen.jpg'],
                            'singleroom' => ['singleroombathroom.jpg', 'singleroomkitchen.jpg', 'singleroomworkarea.jpg'],
                            'doubleroom' => ['doubleroombathroom.jpg', 'doubleroomkitchen.jpg', 'doubleroombalcony.jpg'],
                            'suiteroom' => ['suiteroombalcony.jpg', 'suiteroomkitchen.jpg'],
                            'seaside' => ['seasidebathroom.jpg', 'seasidekitchen.jpg']
                        ];

                        if (isset($roomAdditionalImages[$searchPattern])) {
                            foreach ($roomAdditionalImages[$searchPattern] as $imageName) {
                                // Check if file exists (absolute path for server-side check)
                                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/Casa-Aurelia/images/' . $imageName;
                                if (file_exists($absolutePath)) {
                                    $room_images[] = $imageName;
                                }
                            }
                        }
                    }

                    if (!empty($room_images)):
                        ?>
                        <div id="admin-carousel-<?php echo $room['id']; ?>" class="carousel slide h-full" data-bs-ride="carousel">
                            <?php if (count($room_images) > 1): ?>
                                <div class="carousel-indicators mb-2">
                                    <?php for ($i = 0; $i < count($room_images); $i++): ?>
                                        <button type="button" data-bs-target="#admin-carousel-<?php echo $room['id']; ?>"
                                            data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active"' : ''; ?>></button>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <div class="carousel-inner h-full">
                                <?php foreach ($room_images as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> h-full">
                                        <img src="<?php echo IMAGES_PATH . htmlspecialchars(trim($image)); ?>"
                                            class="d-block w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                            alt="<?php echo $index === 0 ? 'Main View' : 'Room View ' . ($index + 1); ?>"
                                            onerror="this.src='<?php echo IMAGES_PATH; ?>default-room.jpg'">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($room_images) > 1): ?>
                                <button class="carousel-control-prev z-20" type="button"
                                    data-bs-target="#admin-carousel-<?php echo $room['id']; ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon transform scale-75" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next z-20" type="button"
                                    data-bs-target="#admin-carousel-<?php echo $room['id']; ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon transform scale-75" aria-hidden="true"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo IMAGES_PATH; ?>default-room.jpg" class="w-full h-full object-cover" alt="Default Room">
                    <?php endif; ?>
                </div>

                <!-- Card Body -->
                <div class="p-8 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-4 gap-4">
                        <h3 class="text-2xl font-bold text-white group-hover:text-yellow-600 transition-colors uppercase tracking-wide flex-1"
                            style="font-family: 'AureliaLight';">
                            <?php echo htmlspecialchars($room['room_name']); ?>
                        </h3>
                        <div class="text-right shrink-0">
                            <span
                                class="block text-2xl font-bold text-yellow-600">$<?php echo formatPrice($room['price']); ?></span>
                            <span class="text-xs text-gray-400">/ night</span>
                        </div>
                    </div>

                    <!-- Features Summary -->
                    <div class="flex flex-wrap gap-4 mb-6 text-sm text-gray-400 border-b border-gray-700 pb-6">
                        <?php
                        switch ($room['room_name']) {
                            case 'Penthouse Suite':
                                echo '<span class="flex items-center gap-2"><i class="fas fa-mountain text-yellow-500"></i> City View</span>';
                                echo '<span class="flex items-center gap-2"><i class="fas fa-wifi text-yellow-500"></i> Free WiFi</span>';
                                break;
                            case 'Bridal Suite':
                                echo '<span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-500"></i> King Bed</span>';
                                echo '<span class="flex items-center gap-2"><i class="fas fa-glass-cheers text-yellow-500"></i> Champagne</span>';
                                break;
                            default:
                                echo '<span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-500"></i> King Bed</span>';
                                echo '<span class="flex items-center gap-2"><i class="fas fa-wifi text-yellow-500"></i> Free WiFi</span>';
                        }
                        ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-6 relative">
                        <p class="text-gray-400 line-clamp-3 leading-relaxed text-sm">
                            <?php echo htmlspecialchars($room['description']); ?>
                        </p>
                    </div>

                    <!-- Action Area -->
                    <div class="mt-auto space-y-3">
                        <?php if ($room['is_booked'] > 0 && !empty($room['next_available_date'])): ?>
                            <p class="text-center text-xs text-red-500 font-medium">
                                Free on: <?php echo date('M j, Y', strtotime($room['next_available_date'])); ?>
                            </p>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-3">
                            <button
                                class="edit-room book_now w-full py-2.5 font-bold rounded-full transition-all flex items-center justify-center gap-2"
                                data-room-id="<?php echo $room['id']; ?>"
                                data-room-name="<?php echo htmlspecialchars($room['room_name']); ?>"
                                data-room-price="<?php echo $room['price']; ?>"
                                data-room-description="<?php echo htmlspecialchars($room['description'] ?? ''); ?>"
                                data-room-image="<?php echo htmlspecialchars($room['room_image'] ?? ''); ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button
                                class="delete-room details w-full py-2.5 font-bold rounded-full transition-all flex items-center justify-center gap-2"
                                data-room-id="<?php echo $room['id']; ?>">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div
        class="text-center py-20 bg-gray-800/50 backdrop-blur-sm rounded-3xl shadow-sm border border-dashed border-gray-700">
        <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-600">
            <i class="fas fa-door-closed text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-white mb-2">No Rooms Found</h3>
        <p class="text-gray-400 mb-6">Get started by adding your first room to the system.</p>
        <button class="px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl transition-colors"
            data-bs-toggle="modal" data-bs-target="#addRoomModal">
            Create Room
        </button>
    </div>
<?php endif; ?>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-[2rem] border-0 shadow-2xl overflow-hidden bg-gray-900">
            <!-- Hero Header -->
            <div class="modal-header border-0 p-0 relative h-48 bg-gray-800">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent z-10"></div>
                <div class="absolute inset-0 flex items-center px-10 z-20">
                    <h5 class="modal-title font-bold text-3xl text-white" style="font-family: 'AureliaLight';">
                        <span class="text-yellow-500 italic">Add</span> New Room
                    </h5>
                </div>
                <button type="button"
                    class="btn-close btn-close-white absolute top-6 right-6 z-30 bg-white/10 p-2 rounded-full"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-8 pt-4">
                <form id="addRoomForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="roomName" class="block text-xs font-bold text-gray-400 uppercase mb-2">Room
                                Name</label>
                            <input type="text"
                                class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                                id="roomName" required>
                        </div>
                        <div>
                            <label for="roomPrice" class="block text-xs font-bold text-gray-400 uppercase mb-2">Price
                                per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-500">$</span>
                                <input type="number"
                                    class="w-full pl-8 pr-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                                    id="roomPrice" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="roomImage" class="block text-xs font-bold text-gray-400 uppercase mb-2">Image
                            Filename</label>
                        <input type="text"
                            class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                            id="roomImage" placeholder="e.g. room.jpg">
                        <small class="text-gray-500 text-xs mt-1 block">Enter the filename from the images
                            folder</small>
                    </div>
                    <div class="mb-4">
                        <label for="roomDescription"
                            class="block text-xs font-bold text-gray-400 uppercase mb-2">Description</label>
                        <textarea
                            class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                            id="roomDescription" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-8 py-6 pt-0">
                <button type="button"
                    class="px-6 py-3 rounded-full text-gray-400 font-bold hover:bg-white/10 transition-all"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                    class="px-8 py-3 rounded-full bg-yellow-600 text-white font-bold hover:bg-yellow-700 transition-all shadow-lg shadow-yellow-600/20"
                    id="saveRoom">Save Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-[2rem] border-0 shadow-2xl overflow-hidden bg-gray-900">
            <!-- Hero Header -->
            <div class="modal-header border-0 p-0 relative h-48 bg-gray-800" id="editModalHeader">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent z-10"></div>
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <img id="editModalHeroImg" src=""
                        class="w-full h-full object-cover blur-[2px] opacity-40 transition-all duration-700" alt="">
                </div>
                <div class="absolute inset-0 flex items-center px-10 z-20">
                    <h5 class="modal-title font-bold text-3xl text-white" style="font-family: 'AureliaLight';">
                        <span class="text-yellow-500 italic">Edit</span> Room Details
                    </h5>
                </div>
                <button type="button"
                    class="btn-close btn-close-white absolute top-6 right-6 z-30 bg-white/10 p-2 rounded-full"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-8 pt-4">
                <form id="editRoomForm">
                    <input type="hidden" id="editRoomId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="editRoomName" class="block text-xs font-bold text-gray-400 uppercase mb-2">Room
                                Name</label>
                            <input type="text"
                                class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                                id="editRoomName" required>
                        </div>
                        <div>
                            <label for="editRoomPrice"
                                class="block text-xs font-bold text-gray-400 uppercase mb-2">Price per Night</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-500">$</span>
                                <input type="number"
                                    class="w-full pl-8 pr-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                                    id="editRoomPrice" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="editRoomImage" class="block text-xs font-bold text-gray-400 uppercase mb-2">Image
                            Filename</label>
                        <input type="text"
                            class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                            id="editRoomImage" placeholder="e.g. room.jpg">
                    </div>
                    <div class="mb-4">
                        <label for="editRoomDescription"
                            class="block text-xs font-bold text-gray-400 uppercase mb-2">Description</label>
                        <textarea
                            class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all outline-none"
                            id="editRoomDescription" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 px-8 py-6 pt-0">
                <button type="button"
                    class="px-6 py-3 rounded-full text-gray-400 font-bold hover:bg-white/10 transition-all"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                    class="px-8 py-3 rounded-full bg-yellow-600 text-white font-bold hover:bg-yellow-700 transition-all shadow-lg shadow-yellow-600/20"
                    id="updateRoom">Update Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Modals and JS (Detail Modal copied from view_rooms for consistency) -->
<div class="modal fade" id="roomDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl bg-gray-900">
            <!-- Header with Image Background -->
            <div class="modal-header border-0 p-0 relative h-64 bg-gray-900">
                <button type="button"
                    class="btn-close absolute top-4 right-4 z-50 bg-white opacity-100 rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors"
                    data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="absolute inset-0 z-0" id="modalHeroImageContainer">
                    <!-- Hero Video/Image injected here -->
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10 pointer-events-none">
                </div>
                <div class="absolute bottom-0 left-0 p-8 z-20 text-white w-full flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-bold mb-1" id="modalRoomTitle" style="font-family: 'AureliaLight';">
                            Room Name</h2>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400 text-xl font-bold" id="modalRoomPrice">$0</span>
                            <span class="text-white/70 text-sm">/ night</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body p-8 bg-gray-900">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Info -->
                    <div class="lg:col-span-2 space-y-6 text-white">
                        <div>
                            <h4 class="text-white font-bold mb-3 uppercase tracking-wider text-xs">Description</h4>
                            <p class="text-gray-400 leading-relaxed" id="modalRoomDesc">Description goes here...</p>
                        </div>

                        <div>
                            <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Room Amenities</h4>
                            <div class="grid grid-cols-2 gap-4" id="modalRoomFeatures">
                                <!-- Features injected here -->
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar / Booking Status -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 h-full flex flex-col justify-center text-center space-y-6 shadow-inner"
                            id="modalBookingStatus">
                            <!-- Status injected here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden bg-gray-900">
            <div class="modal-header border-0 bg-red-900/20 p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <h5 class="modal-title font-bold text-xl text-white">Delete Room</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-6 text-center">
                <p class="text-gray-300 mb-2">Are you sure you want to delete this room?</p>
                <p class="text-gray-500 text-sm">This action cannot be undone.</p>
                <input type="hidden" id="deleteRoomId" value="">
            </div>
            <div class="modal-footer border-0 p-6 pt-0 flex justify-center gap-3">
                <button type="button"
                    class="px-6 py-2.5 rounded-xl text-gray-400 font-bold hover:bg-gray-800 transition-all"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button"
                    class="px-6 py-2.5 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-all"
                    id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt mr-2"></i>Delete Room
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar for Modal */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #1a202c;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #4a5568;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #718096;
    }

    #roomDetailModal .modal-dialog {
        max-width: calc(90vw - 100px);
        max-height: calc(110vh - 200px);
        margin: 90px auto;
        transition: all 0.3s ease;
    }

    #roomDetailModal .modal-content {
        height: 100%;
        max-height: calc(130vh - 200px);
    }

    @media (max-width: 1024px) {
        #roomDetailModal .modal-dialog {
            max-width: calc(100vw - 60px);
            max-height: calc(100vh - 170px);
            margin: 80px auto;
        }

        #roomDetailModal .modal-content {
            max-height: calc(120vh - 170px);
        }
    }

    @media (max-width: 768px) {
        #roomDetailModal .modal-dialog {
            max-width: calc(90vw - 20px);
            max-height: calc(107vh - 180px);
            margin: 90px auto;
        }

        #roomDetailModal .modal-content {
            max-height: calc(120vh - 170px);
        }

        .modal-header {
            height: 180px !important;
        }

        .modal-body {
            padding: 1.5rem !important;
        }
    }
</style>

<script>
    const roomsData = <?php echo json_encode($rooms_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '[]'; ?>;
    const roomVideos = {
        penthousesuite: '<?php echo SITE_ROOT; ?>Videos/Penthouse.mp4',
        bridalsuite: '<?php echo SITE_ROOT; ?>Videos/bridal.mp4',
        honeymoonsuite: '<?php echo SITE_ROOT; ?>Videos/honeymoon.mp4',
        doubleroom: '<?php echo SITE_ROOT; ?>Videos/double.mp4',
        queenroom: '<?php echo SITE_ROOT; ?>Videos/queen.mp4',
        queens: '<?php echo SITE_ROOT; ?>Videos/queen.mp4',
        kingroom: '<?php echo SITE_ROOT; ?>Videos/king.mp4',
        kings: '<?php echo SITE_ROOT; ?>Videos/king.mp4',
        seasideview: '<?php echo SITE_ROOT; ?>Videos/seaside.mp4',
        singleroom: '<?php echo SITE_ROOT; ?>Videos/single.mp4',
        suiteroom: '<?php echo SITE_ROOT; ?>Videos/suite.mp4'
    };

    function getRoomVideoSrc(roomName) {
        if (!roomName) return '<?php echo SITE_ROOT; ?>Videos/default.mp4';
        const normalized = roomName.toLowerCase().replace(/[^a-z0-9]/g, '');
        if (roomVideos[normalized]) return roomVideos[normalized];
        const entry = Object.entries(roomVideos).find(([key]) => normalized.includes(key));
        return entry ? entry[1] : '<?php echo SITE_ROOT; ?>Videos/default.mp4';
    }

    function getRoomFeatures(roomName) {
        const features = {
            'Penthouse Suite': ['fa-mountain:City View', 'fa-couch:Living Area', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV', 'fa-bath:Luxury Bath', 'fa-utensils:Kitchenette'],
            'Bridal Suite': ['fa-bed:King Bed', 'fa-glass-cheers:Champagne', 'fa-bath:Luxury Bath', 'fa-heart:Romantic', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV'],
            'Honeymoon Suite': ['fa-bed:King Bed', 'fa-heart:Romantic', 'fa-bath:Jacuzzi', 'fa-mountain:Ocean View', 'fa-wifi:Free WiFi'],
            'default': ['fa-bed:Comfortable Bed', 'fa-wifi:Free WiFi', 'fa-tv:Smart TV', 'fa-bath:Private Bath', 'fa-wind:AC', 'fa-coffee:Coffee Maker']
        };
        for (let key in features) {
            if (key !== 'default' && roomName.toLowerCase().includes(key.toLowerCase())) return features[key];
        }
        return features.default;
    }

    function toggleAudio() {
        const video = document.querySelector('#modalHeroImageContainer video');
        if (!video) return;
        video.muted = !video.muted;
        const btn = document.getElementById('audioToggleBtn');
        if (video.muted) {
            btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            btn.classList.add('bg-red-500', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-900');
        } else {
            btn.innerHTML = '<i class="fas fa-volume-up"></i>';
            btn.classList.remove('bg-red-500', 'text-white');
            btn.classList.add('bg-white', 'text-gray-900');
        }
    }

    function updateAudioButton(isMuted) {
        const btn = document.getElementById('audioToggleBtn');
        if (!btn) return;
        if (isMuted) {
            btn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            btn.classList.add('bg-red-500', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-900');
        } else {
            btn.innerHTML = '<i class="fas fa-volume-up"></i>';
            btn.classList.remove('bg-red-500', 'text-white');
            btn.classList.add('bg-white', 'text-gray-900');
        }
    }

    function showRoomDetails(roomId, modalBS) {
        const room = roomsData.find(r => r.id == roomId);
        if (!room) return;

        document.getElementById('modalRoomTitle').textContent = room.room_name;
        document.getElementById('modalRoomPrice').textContent = '$' + parseFloat(room.price).toFixed(2);

        const videoSrc = getRoomVideoSrc(room.room_name);
        const heroContainer = document.getElementById('modalHeroImageContainer');

        heroContainer.innerHTML = `
            <video class="w-full h-full object-cover" autoplay loop playsinline>
                <source src="${videoSrc}" type="video/mp4">
            </video>
            <button onclick="toggleAudio()" id="audioToggleBtn" 
                class="absolute bottom-4 right-4 z-30 w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-transform hover:scale-110 bg-white text-gray-900">
                <i class="fas fa-volume-up"></i>
            </button>
        `;

        const video = heroContainer.querySelector('video');
        video.muted = false;
        const playPromise = video.play();
        if (playPromise !== undefined) {
            playPromise.then(_ => updateAudioButton(false))
                .catch(error => {
                    video.muted = true;
                    video.play();
                    updateAudioButton(true);
                });
        }

        document.getElementById('modalRoomDesc').textContent = room.description || 'Experience luxury and comfort in this beautifully appointed room.';

        const features = getRoomFeatures(room.room_name);
        const featureContainer = document.getElementById('modalRoomFeatures');
        featureContainer.innerHTML = features.map(feat => {
            const [icon, text] = feat.split(':');
            return `
                <div class="flex items-center gap-3 p-3 bg-gray-800 rounded-xl">
                    <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-yellow-500 shadow-sm">
                        <i class="fas ${icon}"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-300">${text}</span>
                </div>
            `;
        }).join('');

        const statusContainer = document.getElementById('modalBookingStatus');
        const statusIcon = room.is_booked > 0 ? 'fa-calendar-times' : 'fa-check-circle';
        const statusColor = room.is_booked > 0 ? 'red' : 'emerald';
        const statusText = room.is_booked > 0 ? 'Occupied' : 'Available';

        statusContainer.innerHTML = `
            <div class="w-12 h-12 bg-${statusColor}-900/30 text-${statusColor}-500 rounded-full flex items-center justify-center mx-auto mb-2">
                <i class="fas ${statusIcon} text-xl"></i>
            </div>
            <h5 class="font-bold text-${statusColor}-500">${statusText}</h5>
            <p class="text-xs text-gray-400">${room.is_booked > 0 ? 'Managing bookings...' : 'Ready for guest booking.'}</p>
            <div class="mt-4 flex gap-2">
                <button class="flex-1 py-2 bg-yellow-600 text-white rounded-lg font-bold text-sm" onclick="openEditFromModal(${room.id})">Edit Room</button>
            </div>
        `;

        modalBS.show();
    }

    function openEditFromModal(roomId) {
        const modalEl = document.getElementById('roomDetailModal');
        const modalBS = bootstrap.Modal.getInstance(modalEl);
        if (modalBS) modalBS.hide();

        // Find and click the corresponding edit button
        const editBtn = document.querySelector(`.edit-room[data-room-id="${roomId}"]`);
        if (editBtn) editBtn.click();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const detailModalEl = document.getElementById('roomDetailModal');
        const detailModalBS = new bootstrap.Modal(detailModalEl);

        // Click handler for card (detail view)
        document.querySelectorAll('.room-clickable').forEach(card => {
            card.addEventListener('click', function (e) {
                if (e.target.closest('button')) return;
                const roomId = this.dataset.roomId;
                showRoomDetails(roomId, detailModalBS);
            });
        });

        // Edit room
        document.querySelectorAll('.edit-room').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation();
                const roomId = this.dataset.roomId;
                const roomName = this.dataset.roomName;
                const roomPrice = this.dataset.roomPrice;
                const roomDescription = this.dataset.roomDescription;
                const roomImage = this.dataset.roomImage;

                document.getElementById('editRoomId').value = roomId;
                document.getElementById('editRoomName').value = roomName;
                document.getElementById('editRoomPrice').value = roomPrice;
                document.getElementById('editRoomDescription').value = roomDescription;
                document.getElementById('editRoomImage').value = roomImage;

                // Update Hero Image in Edit Modal
                const heroImg = document.getElementById('editModalHeroImg');
                if (heroImg) {
                    heroImg.src = `<?php echo IMAGES_PATH; ?>${roomImage || 'default-room.jpg'}`;
                }

                new bootstrap.Modal(document.getElementById('editRoomModal')).show();
            });
        });

        // Delete room - open confirmation modal
        let deleteConfirmModal = null;

        let forceDeleteMode = false; // Global flag for force delete

        function getDeleteModal() {
            const el = document.getElementById('deleteConfirmModal');
            // Move modal to body if needed
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
            // Get or create Bootstrap Modal instance
            let instance = bootstrap.Modal.getInstance(el);
            if (!instance) {
                instance = new bootstrap.Modal(el);
            }
            return instance;
        }

        document.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('.delete-room');
            if (deleteBtn) {
                e.stopPropagation();
                e.preventDefault();

                const roomId = deleteBtn.dataset.roomId;
                console.log('Delete button clicked for room:', roomId);

                // Reset force mode when opening modal normally
                forceDeleteMode = false;
                
                // Store room ID and show confirmation modal
                document.getElementById('deleteRoomId').value = roomId;
                getDeleteModal().show();
            }
        });

        // Handle confirm delete button click
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function () {
            const roomId = document.getElementById('deleteRoomId').value;
            const btn = this;

            console.log('Confirmed deletion for room:', roomId, 'Force:', forceDeleteMode);

            // Show loading state
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
            btn.disabled = true;

            try {
                let body = `room_id=${roomId}`;
                if (forceDeleteMode) {
                    body += '&force=true';
                }
                
                const response = await fetch('delete_room.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body
                });

                const text = await response.text();
                console.log('Delete response:', response.status, text);

                if (response.ok) {
                    getDeleteModal().hide();
                    forceDeleteMode = false;
                    location.reload();
                } else if (response.status === 400 && text.includes('active booking')) {
                    // Has active bookings - offer force delete
                    getDeleteModal().hide();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    
                    setTimeout(() => {
                        if (confirm(text + '\n\nDo you want to FORCE DELETE this room?\n⚠️ WARNING: This will also DELETE all associated bookings!')) {
                            // Set force mode and re-trigger delete
                            forceDeleteMode = true;
                            document.getElementById('deleteRoomId').value = roomId;
                            // Directly call the delete instead of using modal
                            performForceDelete(roomId, btn, originalText);
                        }
                    }, 300);
                } else {
                    getDeleteModal().hide();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    forceDeleteMode = false;
                    setTimeout(() => {
                        alert('Error: ' + text);
                    }, 300);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Connection error: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
                forceDeleteMode = false;
            }
        });
        
        // Separate function for force delete
        async function performForceDelete(roomId, btn, originalText) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Force Deleting...';
            btn.disabled = true;
            
            try {
                const response = await fetch('delete_room.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `room_id=${roomId}&force=true`
                });

                const text = await response.text();
                console.log('Force delete response:', response.status, text);

                if (response.ok) {
                    forceDeleteMode = false;
                    location.reload();
                } else {
                    alert('Force delete failed: ' + text);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    forceDeleteMode = false;
                }
            } catch (error) {
                console.error('Force delete error:', error);
                alert('Connection error: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
                forceDeleteMode = false;
            }
        }

        document.getElementById('saveRoom').addEventListener('click', () => {
            const roomName = document.getElementById('roomName').value;
            const roomPrice = document.getElementById('roomPrice').value;
            const roomDescription = document.getElementById('roomDescription').value;
            const roomImage = document.getElementById('roomImage').value;
            if (roomName && roomPrice) addNewRoom(roomName, roomPrice, roomDescription, roomImage);
        });

        document.getElementById('updateRoom').addEventListener('click', () => {
            const roomId = document.getElementById('editRoomId').value;
            const roomName = document.getElementById('editRoomName').value;
            const roomPrice = document.getElementById('editRoomPrice').value;
            const roomDescription = document.getElementById('editRoomDescription').value;
            const roomImage = document.getElementById('editRoomImage').value;
            if (roomId && roomName && roomPrice) updateRoom(roomId, roomName, roomPrice, roomDescription, roomImage);
        });

        // Modals cleanup and video pause
        ['roomDetailModal', 'addRoomModal', 'editRoomModal', 'deleteConfirmModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('hidden.bs.modal', function () {
                    const video = el.querySelector('video');
                    if (video) { video.pause(); video.currentTime = 0; }
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.style.overflow = '';
                });
            }
        });

        // Move modals to body to avoid clipping
        setTimeout(() => {
            ['roomDetailModal', 'addRoomModal', 'editRoomModal', 'deleteConfirmModal'].forEach(id => {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) document.body.appendChild(el);
            });
        }, 500);
    });

    // Helper functions - using direct relative paths since we're in /admin/
    async function deleteRoom(roomId) {
        try {
            const response = await fetch('delete_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `room_id=${roomId}`
            });

            const text = await response.text();
            console.log('Delete response:', response.status, text);

            if (response.ok) {
                location.reload();
            } else {
                alert('Error deleting room: ' + text);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Connection error while deleting room.');
        }
    }

    async function addNewRoom(roomName, roomPrice, roomDescription, roomImage) {
        try {
            const response = await fetch('add_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `room_name=${encodeURIComponent(roomName)}&price=${roomPrice}&description=${encodeURIComponent(roomDescription)}&room_image=${encodeURIComponent(roomImage)}`
            });

            const text = await response.text();
            console.log('Add response:', response.status, text);

            if (response.ok) {
                location.reload();
            } else {
                alert('Error adding room: ' + text);
            }
        } catch (error) {
            console.error('Add error:', error);
            alert('Connection error while adding room.');
        }
    }

    async function updateRoom(roomId, roomName, roomPrice, roomDescription, roomImage) {
        try {
            const response = await fetch('update_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `room_id=${roomId}&room_name=${encodeURIComponent(roomName)}&price=${roomPrice}&description=${encodeURIComponent(roomDescription)}&room_image=${encodeURIComponent(roomImage)}`
            });

            const text = await response.text();
            console.log('Update response:', response.status, text);

            if (response.ok) {
                location.reload();
            } else {
                alert('Error updating room: ' + text);
            }
        } catch (error) {
            console.error('Update error:', error);
            alert('Connection error while updating room.');
        }
    }
</script>

</main>
</div>
</div>

<?php include('../includes/footer.php'); ?>