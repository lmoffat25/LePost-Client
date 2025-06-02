# Credits Display Debugging Guide

## 🔧 **Fixes Applied**

✅ **Fixed Error Handling**: Added proper error handling in `DashboardTab::get_api_credits_info()`
✅ **Fixed View Errors**: Added error display in dashboard statistics view
✅ **Fixed Duplicate Headers**: Removed duplicate headers in API request
✅ **Added Logging**: Added debug logging for credit retrieval
✅ **Fixed API Response Format**: Updated to handle actual API response structure
✅ **Multiple Endpoints**: Now tries multiple credit endpoints automatically

## 🎉 **SOLUTION IMPLEMENTED**

✅ **Created new `/account-info` endpoint** in LePost API server
✅ **Updated LePost-Client** to use the new endpoint
✅ **Fixed credit retrieval** for direct clients

## 🔧 **What Was Done**

1. **Added `/wp-json/le-post/v1/account-info` endpoint** to LePost's AuthController
2. **Endpoint returns**:
   - Account validation info
   - **Credit information** using LP_CreditManager
   - Detailed breakdown for sub-keys vs direct clients
3. **Updated LePost-Client** to prioritize this new endpoint

## 🔍 **Credit Storage System**

**For Direct Clients** (like user ID 14):
- Credits stored in user meta: `lp_api_credits`  
- Retrieved via: `get_user_meta($user_id, 'lp_api_credits', true)`

**For Sub-Keys:**
- Credits stored in `wp_lepost_sub_keys` table
- Fields: `balance_current` + `balance_extra`

## 📋 **New API Response Format**

The `/account-info` endpoint now returns:
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "key_type": "direct", 
    "user_id": 14,
    "account_status": "active",
    "credits": {
      "credits_remaining": 150,
      "key_type": "direct",
      "last_updated": "2025-06-02 12:00:00"
    }
  }
}
```

## 🐛 **Debugging Steps**

### **1. Enable Debug Mode**
Add `&debug=1` to your dashboard URL:
```
/wp-admin/admin.php?page=lepost-client&tab=dashboard&debug=1
```

### **2. Check Error Logs**
Look for these log entries:
- `LePost API: Successfully got credits from /account-info endpoint`
- `LePost API account-info: Response Code: 200`
- `LePost API account-info: Extracted credits: [NUMBER]`
- `LePost Client Dashboard: Credits retrieved successfully`

### **3. What Should Happen Now**

**✅ Success Case:**
- Shows actual credit count from user meta
- Shows refresh time
- Debug shows account-info endpoint response

**❌ If Still Failing:**
- Check if AuthController changes were applied
- Verify CreditManager is working
- Check user meta for `lp_api_credits`

### **4. Manual Testing**

Test the new account-info endpoint:
```bash
curl -X POST "YOUR_API_URL/wp-json/le-post/v1/account-info" \
  -H "Content-Type: application/json" \
  -d '{"api_key": "YOUR_API_KEY"}'
```

Expected response:
```json
{
  "success": true,
  "data": {
    "credits": {"credits_remaining": 123}
  }
}
```

### **5. Check User Credits Directly**

In WordPress admin/database, check:
```sql
SELECT meta_value 
FROM wp_usermeta 
WHERE user_id = 14 
AND meta_key = 'lp_api_credits';
```

## 🎯 **Root Cause & Solution Summary**

- ❌ **Problem**: No API endpoint returned credit information
- ✅ **Solution**: Created `/account-info` endpoint in LePost
- 🔧 **How**: Uses LP_CreditManager to get credits from user meta
- 📊 **Result**: LePost-Client can now display actual credits

## 📝 **Files Modified**

1. **LePost/src/API/Controllers/AuthController.php**: Added account-info endpoint
2. **LePost-Client/src/Api/Api.php**: Updated to use new endpoint
3. **LePost-Client dashboard**: Already handles credit display

The credit display should now work correctly! 🚀

## 📝 **Next Steps**

1. **Check logs** after applying the fix
2. **Add debug=1** to see detailed API responses
3. **If credits still show 0**: Your API server needs credit endpoints
4. **Contact API developer** to add credit endpoints if missing

## 🎯 **Root Cause Summary**

- ✅ API key validation **works**
- ❌ Credit endpoints **missing** from API server
- 🔧 Plugin now **handles this gracefully**

The plugin will now work correctly but show 0 credits until proper credit endpoints are added to your API server. 