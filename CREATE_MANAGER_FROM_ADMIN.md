# How to Create Apartment Manager from Super Admin Panel

## Method: Using Tenant Resource

The easiest way to create apartment managers is through the Tenant (Apartment Complex) resource.

### Steps:

1. **Login to Super Admin Panel**
   - Go to: `http://localhost/admin`
   - Login with your super admin credentials

2. **Navigate to Apartment Complexes**
   - Click on "Apartment Complexes" in the sidebar (under "Tenant Management")

3. **View or Edit a Tenant**
   - Click on any apartment complex to view/edit it
   - OR click the "View" button (eye icon) next to a tenant

4. **Go to "Apartment Managers" Tab**
   - When viewing/editing a tenant, you'll see a tab called "Apartment Managers"
   - Click on this tab

5. **Create New Manager**
   - Click the "Add Apartment Manager" button
   - Fill in the form:
     - **Name**: Manager's full name
     - **Email**: Manager's email address
     - **Password**: Set a secure password (min 8 characters)
     - **Phone**: Manager's phone number (optional)
   - Click "Create"

6. **Manager Created!**
   - The manager will automatically be:
     - Assigned to the current tenant
     - Set with role 'apartment_manager'
     - Ready to login at `/app`

## Login Details

After creating, the manager can login at:
- **URL**: `http://localhost/app`
- **Email**: The email you entered
- **Password**: The password you set

## Features

- ✅ Automatically assigns tenant_id
- ✅ Sets role to 'apartment_manager'
- ✅ Shows all managers for that tenant
- ✅ Can edit/delete managers
- ✅ Full CRUD operations

## Alternative: Direct User Creation

You can also create managers directly, but they must have:
- `tenant_id` set to the apartment complex ID
- `role` set to 'apartment_manager'

The relation manager method is recommended as it ensures proper assignment.

