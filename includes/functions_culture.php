<?php
/**
 * Functions for culture quiz and culture matches
 */

/**
 * Enhanced version of getUserCultureResults with compatibility fixes
 * 
 * @param int $userId The user ID
 * @return array|null The culture profile or null if not found
 */
function getEnhancedUserCultureResults($userId) {
    $result = fetchRow(
        "SELECT * FROM user_culture_results WHERE user_id = ?",
        'i',
        [$userId]
    );
    
    if ($result) {
        $result['culture_profile'] = json_decode($result['culture_profile'], true);
        
        // Check if the format is the old style (with options array)
        // and values are direct key-value pairs like 'focused' => 'focused'
        if (isset($result['culture_profile']['options']) && 
            isset($result['culture_profile']['values']) && 
            is_array($result['culture_profile']['values'])) {
            
            // The data format is already compatible with our view code
            // We just need to make sure it works with the rest of our code
            
            // No changes needed, the view code expects:
            // $userCultureResults['culture_profile']['values'] as an associative array
        }
    }
    
    return $result;
}

/**
 * Display culture attribute names in a friendly format
 * 
 * @param string $attribute The attribute name
 * @return string The formatted attribute name
 */
function formatCultureAttributeName($attribute) {
    $attributeNames = [
        'work_environment' => 'Work Environment',
        'overtime' => 'Overtime Expectations',
        'management' => 'Management Style',
        'work_life_balance' => 'Work-Life Balance',
        'dress_code' => 'Dress Code',
        'communication' => 'Communication Style',
        'decision_making' => 'Decision Making',
        'innovation' => 'Innovation',
        'social_events' => 'Social Events',
        'feedback' => 'Feedback Style'
    ];
    
    // If the attribute is a direct name like 'focused', 'dedicated', etc.
    if (!isset($attributeNames[$attribute])) {
        return ucfirst($attribute);
    }
    
    return $attributeNames[$attribute];
}

/**
 * Enhanced culture fit score calculator that handles both data formats
 * 
 * @param int $userId The user ID
 * @param int $companyId The company ID
 * @return array The fit score and matching attributes
 */
function enhancedCalculateCultureFitScore($userId, $companyId) {
    $userCulture = getEnhancedUserCultureResults($userId);
    $companyCulture = getCompanyCulture($companyId);
    
    if (!$userCulture || !$companyCulture) {
        return [
            'score' => 0,
            'matches' => [],
            'mismatches' => []
        ];
    }
    
    $userValues = $userCulture['culture_profile']['values'];
    $companyValues = isset($companyCulture['values']) ? $companyCulture['values'] : [];
    
    $matches = [];
    $mismatches = [];
    $matchCount = 0;
    
    // Debug log to see the actual data formats
    error_log("Enhanced - User culture values: " . json_encode($userValues));
    error_log("Enhanced - Company culture values: " . json_encode($companyValues));
    
    // Compare each user value with company values
    foreach ($userValues as $userAttribute => $userValue) {
        $matched = false;
        
        // User data format: $userAttribute may be something like "collaborative" 
        // and $userValue is also "collaborative"
        // Company data format: key is attribute name like "work_environment" and value is "collaborative"
        
        // Method 1: Direct match if company has the same attribute-value pair
        if (isset($companyValues[$userAttribute]) && $companyValues[$userAttribute] == $userValue) {
            $matches[] = $userAttribute;
            $matchCount++;
            $matched = true;
        } 
        // Method 2: Check if the user's attribute name is a value in any of the company's attributes
        else {
            foreach ($companyValues as $companyAttribute => $companyValue) {
                // If either the attribute name or value from user matches company's value
                if ($userAttribute == $companyValue || $userValue == $companyValue) {
                    $matches[] = $companyAttribute;
                    $matchCount++;
                    $matched = true;
                    break;
                }
            }
        }
        
        if (!$matched && isset($companyValues[$userAttribute])) {
            $mismatches[] = $userAttribute;
        }
    }
    
    // New scoring system: 10 points per match, max 100 points
    $score = min(100, $matchCount * 10);
    
    return [
        'score' => $score,
        'matches' => $matches,
        'mismatches' => $mismatches
    ];
}

/**
 * Enhanced function to get companies matching user's culture profile
 * 
 * @param int $userId The user ID
 * @param int $limit The maximum number of companies to return
 * @return array Array of companies with match scores
 */
function enhancedGetMatchingCompanies($userId, $limit = 5) {
    $companies = fetchAll("SELECT c.*, u.username FROM companies c JOIN users u ON c.user_id = u.user_id WHERE c.company_culture IS NOT NULL");
    $matches = [];
    
    foreach ($companies as $company) {
        $matchData = enhancedCalculateCultureFitScore($userId, $company['company_id']);
        
        // Only include if there is some match
        if ($matchData['score'] > 0) {
            $company['match_score'] = $matchData['score'];
            $company['matches'] = $matchData['matches'];
            $company['mismatches'] = $matchData['mismatches'];
            $matches[] = $company;
        }
    }
    
    // Sort by match score
    usort($matches, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
    
    // Limit results
    return array_slice($matches, 0, $limit);
}

// The getEnhancedUserCareerHistory function has been removed from this file
// It is now defined only in includes/functions.php to avoid duplicate declaration 