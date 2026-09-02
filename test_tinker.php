$user = \App\Models\User::where('role', 'fieldstaff')->first(); 
auth('api')->login($user); 
$request = \Illuminate\Http\Request::create('/api/field-staff/dashboard', 'GET'); 
$request->headers->set('Authorization', 'Bearer ' . auth('api')->tokenById($user->id)); 
$response = app()->handle($request); 
echo $response->getContent();
