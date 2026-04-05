<?php
/**
 * Project Status Manager
 * Simple system to manage project statuses without rewriting code
 */

// Define available project statuses
define('PROJECT_STATUS_COMPLETED', 'completed');
define('PROJECT_STATUS_IN_PROGRESS', 'in-progress');
define('PROJECT_STATUS_PLANNING', 'planning');
define('PROJECT_STATUS_ON_HOLD', 'on-hold');

/**
 * Get all available project statuses with display names
 */
function getProjectStatuses() {
    return [
        PROJECT_STATUS_COMPLETED => [
            'label' => 'Completed',
            'class' => 'completed',
            'icon' => 'bi-check-circle-fill'
        ],
        PROJECT_STATUS_IN_PROGRESS => [
            'label' => 'In Progress',
            'class' => 'in-progress',
            'icon' => 'bi-arrow-repeat'
        ],
        PROJECT_STATUS_PLANNING => [
            'label' => 'Planning',
            'class' => 'planning',
            'icon' => 'bi-calendar-check'
        ],
        PROJECT_STATUS_ON_HOLD => [
            'label' => 'On Hold',
            'class' => 'on-hold',
            'icon' => 'bi-pause-circle'
        ]
    ];
}

/**
 * Get status information for a specific status
 */
function getProjectStatusInfo($status) {
    $statuses = getProjectStatuses();
    return isset($statuses[$status]) ? $statuses[$status] : $statuses[PROJECT_STATUS_COMPLETED];
}

/**
 * Update project status (simple way to change status)
 * Usage: updateProjectStatus('king-fahd-stadium', PROJECT_STATUS_COMPLETED);
 */
function updateProjectStatus($projectSlug, $newStatus) {
    // This is a simple status override system
    // You can add project-specific status overrides here
    $statusOverrides = [];
    
    // Add your status overrides here
    // Example: $statusOverrides['king-fahd-stadium'] = PROJECT_STATUS_COMPLETED;
    
    return isset($statusOverrides[$projectSlug]) ? $statusOverrides[$projectSlug] : $newStatus;
}

/**
 * Get project status with override support
 */
function getProjectStatus($projectSlug, $originalStatus) {
    // Check if there's an override for this project
    $overrideStatus = updateProjectStatus($projectSlug, $originalStatus);
    
    // Validate that the status exists
    $validStatuses = array_keys(getProjectStatuses());
    return in_array($overrideStatus, $validStatuses) ? $overrideStatus : $originalStatus;
}

/**
 * Quick status update function - EASY TO USE
 * Just add your project slugs and desired statuses here
 */
function getQuickStatusUpdates() {
    return [
        // Format: 'project-slug' => 'new-status'
        // 'king-fahd-stadium' => PROJECT_STATUS_COMPLETED,
        // 'makkah-chilled-water' => PROJECT_STATUS_IN_PROGRESS,
        // 'lamar-towers' => PROJECT_STATUS_ON_HOLD,
        
        // Add more projects as needed
    ];
}

/**
 * Apply quick status updates
 */
function applyQuickStatusUpdates($projectSlug, $originalStatus) {
    $updates = getQuickStatusUpdates();
    
    if (isset($updates[$projectSlug])) {
        return $updates[$projectSlug];
    }
    
    return $originalStatus;
}

/**
 * Get status class for CSS styling
 */
function getStatusClass($status) {
    $statusInfo = getProjectStatusInfo($status);
    return $statusInfo['class'] ?? '';
}

/**
 * Get status label for display
 */
function getStatusLabel($status) {
    $statusInfo = getProjectStatusInfo($status);
    return $statusInfo['label'] ?? $status;
}

/**
 * Get status icon for display
 */
function getStatusIcon($status) {
    $statusInfo = getProjectStatusInfo($status);
    return $statusInfo['icon'] ?? '';
}

?>
