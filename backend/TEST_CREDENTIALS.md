# Test Account Credentials

## Password for ALL test accounts: `11111111`

## Key Test Accounts

### Admin
- Email: `test.admin@cvsu.edu.ph`
- Password: `11111111`
- Role: Admin
- Department: Administration

### Dean
- Email: `dean.rodriguez@cvsu.edu.ph`
- Password: `11111111`
- Role: Dean
- Department: College of Engineering

### Chairpersons (1 per department)
- CS: `maria.garcia@cvsu.edu.ph`
- IT: `john.rivera@cvsu.edu.ph`
- CE: `sarah.reyes@cvsu.edu.ph`
- EE: `michael.cruz@cvsu.edu.ph`
- ME: `anna.delacruz@cvsu.edu.ph`

### Coordinators (5 per department, 25 total)
Email format: `firstname.lastname.coord#.department@cvsu.edu.ph`

Examples:
- `carlos.santos.coord0.computerscience@cvsu.edu.ph`
- `elena.mendoza.coord1.informationtechnology@cvsu.edu.ph`

### Faculty Members (5 per department, 25 total)
Email format: `firstname.lastname.fac#.department@cvsu.edu.ph`

Examples:
- `jennifer.lopez.fac0.computerscience@cvsu.edu.ph`
- `antonio.gonzales.fac1.informationtechnology@cvsu.edu.ph`

## Total: 52 test accounts

## How to Use

1. Run seeder: `php artisan db:seed --class=TestUsersSeeder`
2. Login with any email above
3. Use password: `11111111`

## Note

The bootstrap admin (`admin@cvsu.edu.ph`) uses a different password set in your `.env` file.
Use `test.admin@cvsu.edu.ph` with password `11111111` for testing instead.
