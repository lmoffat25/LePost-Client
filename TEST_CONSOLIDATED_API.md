# 🧪 Testing LePost-Client with Consolidated API

## 📋 **Overview**

This guide helps test the updated LePost-Client integration with the new consolidated API endpoints.

## 🔧 **Updated API Methods**

### **1. Enhanced Connection Verification**
The `verify_connection()` method now:
- ✅ Uses the enhanced `/verify-api-key` endpoint
- ✅ Handles new API response format
- ✅ Stores additional account information (user_id, key_type, account_status)

### **2. Consolidated Account Information**
The `get_account_info()` method now:
- ✅ Uses single `/verify-api-key` endpoint with `include: ['credits', 'usage']`
- ✅ Gets credits and account info in one API call
- ✅ Implements 5-minute caching to reduce API load
- ✅ Handles enhanced API response format

### **3. Quick Credits Access**
New `get_credits()` method:
- ✅ Returns just credit count quickly
- ✅ Uses cached data when available
- ✅ Fallback-safe for error handling

## 🚦 **Testing Checklist**

### **Test 1: Connection Verification**
```php
// In WordPress admin or via WP-CLI
$api = new \LePostClient\Api\Api();
$api->set_api_key('your-test-key');
$result = $api->verify_connection();

// Expected: New response format with enhanced data
print_r($result);
```

**Expected Response:**
```php
Array(
    [success] => 1
    [message] => "Connexion réussie à l'API LePost."
    [data] => Array(
        [is_valid] => 1
        [key_type] => "direct"
        [user_id] => 123
        [account_status] => "active"
    )
)
```

### **Test 2: Account Information with Credits**
```php
$api = new \LePostClient\Api\Api();
$account_info = $api->get_account_info(true); // Force refresh

// Expected: Enhanced response with credits and usage
print_r($account_info);
```

**Expected Response:**
```php
Array(
    [success] => 1
    [credits] => 500
    [account] => Array(
        [is_valid] => 1
        [key_type] => "direct"
        [user_id] => 123
        [account_status] => "active"
        [credits] => Array(
            [credits_remaining] => 500
            [key_type] => "direct"
            [last_updated] => "2024-01-20 10:30:00"
        )
        [usage] => Array(
            [last_30_days] => Array(
                [total_requests] => 45
                [total_credits_used] => 45
            )
        )
    )
    [message] => "Informations du compte récupérées avec succès."
    [cached] => 
    [endpoint] => "verify-api-key-enhanced"
)
```

### **Test 3: Quick Credits Check**
```php
$api = new \LePostClient\Api\Api();
$credits = $api->get_credits();

// Expected: Just the credit count as integer
echo "Available credits: " . $credits;
```

### **Test 4: Dashboard Display**
1. Go to **LePost Client > Dashboard** in WordPress admin
2. Check that credits display correctly
3. Check that error handling works gracefully
4. Test refresh functionality

**Expected Behavior:**
- ✅ Credits display correctly
- ✅ Last updated time shows
- ✅ Refresh button works
- ✅ Error states handle gracefully
- ✅ Debug mode works with `?debug=1`

### **Test 5: Caching Behavior**
```php
// First call - should hit API
$api = new \LePostClient\Api\Api();
$result1 = $api->get_account_info(true);
echo "First call cached: " . ($result1['cached'] ? 'Yes' : 'No') . "\n";

// Second call - should use cache
$result2 = $api->get_account_info(false);
echo "Second call cached: " . ($result2['cached'] ? 'Yes' : 'No') . "\n";
```

**Expected:**
- First call: `cached = false`
- Second call: `cached = true`

## 🐛 **Troubleshooting**

### **Issue: Fatal Error in IdeasManager**
**Error Message:**
```
PHP Fatal error: Cannot use object of type WP_Error as array in IdeasManager.php:308
```

**Cause:** The IdeasManager wasn't properly handling `WP_Error` objects returned by the API.

**✅ Fixed:** Added proper `is_wp_error()` check before accessing array elements.

### **Issue: New API Response Format Not Recognized**
**Error Message:**
```
[ERREUR] Format de réponse non reconnu pour la génération d'idées
Erreur WP_Error lors de la génération d'idées: Format de réponse inattendu lors de la génération des idées.
```

**Cause:** The consolidated API now returns a new response format with `{"success": true, "data": {...}}` wrapper, but the LePost-Client was expecting the old format.

**New API Response Format:**
```json
{
  "success": true,
  "data": {
    "theme": "Les fruits de mer dans la cuisine italienne",
    "ideas": [
      {"title": "...", "explanation": "..."},
      ...
    ]
  },
  "timestamp": "2025-06-02 12:24:48"
}
```

**✅ Fixed:** Updated `generate_ideas()` method to handle the new consolidated API response format while maintaining backward compatibility.

### **Issue: OpenAI API Key Not Configured**
**Error Message:**
```
Erreur: Clé API OpenAI non configurée dans les paramètres du plugin
```

**Cause:** LePost server is trying to use OpenAI but the API key isn't configured.

**Solution:**
1. Go to **LePost WordPress Admin > LePost > Settings**
2. Find **"Clé API OpenAI (ChatGPT)"** field
3. Enter your OpenAI API key (starts with `sk-...`)
4. Select an OpenAI model (recommended: `gpt-4o-mini`)
5. Save settings

**See:** `LePost/OPENAI_CONFIGURATION_GUIDE.md` for detailed instructions.

### **Issue: Credits Show as 0**
**Possible Causes:**
1. API key invalid
2. Server connectivity issues
3. API endpoint changes

**Debug Steps:**
1. Add `?debug=1` to dashboard URL
2. Check WordPress error logs
3. Verify API key in settings
4. Test with `verify_connection()` first

### **Issue: "Account info failed" Error**
**Check:**
1. API server accessibility
2. SSL certificate validity
3. WordPress timeout settings
4. API key permissions

### **Issue: Cached Data Issues**
**Solutions:**
1. Force refresh with `get_account_info(true)`
2. Clear transients: `delete_transient('lepost_client_account_info')`
3. Check cache timeout (currently 5 minutes)

## 📊 **Performance Improvements**

**Before Consolidation:**
- 2 API calls: `/verify-api-key` + `/account-info`
- No caching
- Inconsistent error handling

**After Consolidation:**
- 1 API call: `/verify-api-key` with `include` parameter
- 5-minute caching
- Consistent error handling
- ~50% faster dashboard loading

## 🎯 **Migration Notes**

**Backward Compatibility:**
- ✅ All existing functionality preserved
- ✅ Old `/account-info` endpoint still works
- ✅ API response format enhanced but compatible
- ✅ Error handling improved

**Breaking Changes:**
- ❌ None! Fully backward compatible

## 🔍 **Monitoring**

**WordPress Error Logs:**
Look for entries starting with:
- `LePost API Enhanced:`
- `LePost Client Dashboard:`

**Successful Integration Indicators:**
- Credits display correctly in dashboard
- "verify-api-key-enhanced" appears in debug info
- Response times improved
- Caching working (check debug output)

---

## ✅ **Sign-off Checklist**

- [ ] Connection verification works with new format
- [ ] Credits retrieve correctly in single API call
- [ ] Caching reduces API load
- [ ] Dashboard displays properly
- [ ] Error handling is graceful
- [ ] Debug mode provides useful information
- [ ] Performance improved over old system
- [ ] All tests pass

**🎉 Ready for production when all items checked!** 