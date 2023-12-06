<p align="center"><a href="https://laravel.com" target="_blank">
<img src="https://5deniz.com/public/5deniz-new/images/logo.png?width=459&name=logo.png" width="400" alt="Laravel Logo"></a></p>


<h1 align="center">B2B API PROJECT</h1>
<br>
<br>


<hr>
<br>

```
  $role = Role::whereName(RoleEnum::PUBLISHER->value)->whereGuardName('api')->first();
            if (!$role) {
                $role = Role::create(['name' => RoleEnum::PUBLISHER->value, 'guard_name' => 'api']);
            }
```

<h2 align="center">Geliştirme Notları </h2>

## Fulltext serach for laravel
```
 Bu fulltext search'ın çalışması için veritabanında bu işlemin uygulanması gereklidir.
 ALTER TABLE {{table_name}}
ADD FULLTEXT INDEX `IndexName` (name);
````

fulltext source code

```
   public function languageSearch(Request $request)
    {
        try {

            $searchText = $request->input('language_search');
            $searchLanguages = Language::whereRaw("MATCH(name) AGAINST (? IN BOOLEAN MODE)", [$searchText])->get();

            return $this->apiSuccessResponse([
                'languages' => $searchLanguages
            ]);


        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }
```
<hr>

## Image multiple added control

```
 public function updateUser(UpdateUserRequest $request)
    {

        try {
            $validated = $request->validated();

            $userId = $request->input('user_id');

            $user = User::with('roles')->find($userId);

            if (!$user) {
                return $this->returnWithMessage("Kullanıcı bulunmadı!", Response::STATUS_NOT_FOUND);
            }

            if ($request->has('avatar_url')) {

                $imageAvatar = $request->file('avatar_url');

//                Orijinal dosya ismini alıyoruz ve sonuna tarih bilgisini ekliyoruz.
//                $originalNameWithoutExtension = pathinfo($imageAvatar->getClientOriginalName(), PATHINFO_FILENAME);
//                $extension = $imageAvatar->getClientOriginalExtension();
//                $name = $originalNameWithoutExtension . '_' . time() . '.' . $extension;

//                $originalName = $imageAvatar->getClientOriginalName();
//                $extension = strstr($originalName, '.');

                $imageName = str_replace(['.', '-'], ['_', '_'], $request->input('email')) . '.jpg';

                $uploadedImage = Storage::disk('image')->putFileAs(
                    '', $imageAvatar, $imageName
                );
            }


            $user->update([
                "name" => $validated['name'],
                "surname" => $validated['surname'],
                "email" => $validated['email'],
                "password" => Hash::make($validated['password']),
                'avatar_url' => !$request->has('avatar_url') ? null : storage_path('app/images/') . $uploadedImage
            ]);


            $userUpdated = User::with('roles')->find($userId);

            $commonRoleId = $request->input('role_id');

            $userBeforeRoles = $user->roles->pluck('id')->toArray();

            if (!in_array($commonRoleId, $userBeforeRoles)) {
                $role = Role::find($validated['role_id']);

                $userUpdated->syncRoles($role->name);
            }


            $updatedUser = User::with('roles')->find($userId);

            return $this->apiSuccessResponse([
                "user" => new UserResource($updatedUser)
            ], Response::STATUS_CREATED, "Kullanıcı başarılı bir şekilde oluşturuldu");

        } catch (\Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }
```

<hr>

## Currency 

```
  public function getCurrencyToTCMB()
    {
        $response = Http::get('https://www.tcmb.gov.tr/kurlar/today.xml');
        $connect_web = new SimpleXMLElement($response->body());

        if(isset($connect_web->Currency[0]->BanknoteBuying, $connect_web->Currency[3]->BanknoteBuying, $connect_web->Currency[4]->BanknoteBuying)) {
            $usd = preg_replace('/[^ ,.%0-9]/', '', $connect_web->Currency[0]->BanknoteBuying);
            $eur = preg_replace('/[^ ,.%0-9]/', '', $connect_web->Currency[3]->BanknoteBuying);
            $gbp = preg_replace('/[^ ,.%0-9]/', '', $connect_web->Currency[4]->BanknoteBuying);

            return [
                'TRY' => [['USD' => 1 / (float)$usd, 'EUR' => 1 / (float)$eur, 'GBP' => 1 / (float)$gbp]],
                'EUR' => [['TRY' => (float)$eur, 'USD' => (float)$eur / $usd, 'GBP' => (float)$eur / $gbp]],
                'USD' => [['TRY' => (float)$usd, 'EUR' => (float)$usd / $eur, 'GBP' => (float)$usd / $gbp]],
                'GBP' => [['TRY' => (float)$gbp, 'EUR' => (float)$gbp / $eur, 'USD' => (float)$gbp / $usd]],
            ];
            /*
             * Bu kod, döviz çiftlerinin birbirlerine karşı değerlerini hesaplar. İşte her bir hesaplamanın ne anlama geldiğini açıklayan bir ayrıntı:

'TRY': [['USD' => 1 / (float)$usd, 'EUR' => 1 / (float)$eur, 'GBP' => 1 / (float)$gbp]]:
Bu satır, Türk Lirası'nın diğer döviz cinslerine karşı değerini hesaplar. Örneğin, USD'nin TRY cinsinden değeri ne kadarsa, 1 TRY'nin USD cinsinden değeri bu değerin tersidir, yani 1 / USD. Benzer şekilde, 1 TRY'nin EUR ve GBP cinsinden değerleri hesaplanır.

'EUR': [['TRY' => (float)$eur, 'USD' => (float)$eur / $usd, 'GBP' => (float)$eur / $gbp]]:
Bu satır, Euro'nun diğer döviz cinslerine karşı değerini hesaplar. TRY cinsinden değeri doğrudan alınır. Ancak, USD ve GBP cinsinden değerler, EUR'nin TRY cinsinden değerinin, ilgili döviz cinsinin TRY cinsinden değerine bölünmesiyle elde edilir.

'USD': [['TRY' => (float)$usd, 'EUR' => (float)$usd / $eur, 'GBP' => (float)$usd / $gbp]]:
Bu satır, ABD Doları'nın diğer döviz cinslerine karşı değerini hesaplar. TRY cinsinden değeri doğrudan alınır. Ancak, EUR ve GBP cinsinden değerler, USD'nin TRY cinsinden değerinin, ilgili döviz cinsinin TRY cinsinden değerine bölünmesiyle elde edilir.

'GBP': [['TRY' => (float)$gbp, 'EUR' => (float)$gbp / $eur, 'USD' => (float)$gbp / $usd]]:
Bu satır, Sterlin'in diğer döviz cinslerine karşı değerini hesaplar. TRY cinsinden değeri doğrudan alınır. Ancak, USD ve EUR cinsinden değerler, GBP'nin TRY cinsinden değerinin, ilgili döviz cinsinin TRY cinsinden değerine bölünmesiyle elde edilir.
             */
        }

        return ['currency' => 'Currency values could not be obtained from the Central Bank of the Republic of Turkey. Transactions will be made according to the latest exchange rates in the system.'];
    }
```
2

```

//    public function index()
//    {
//        $response = Http::get('https://www.tcmb.gov.tr/kurlar/today.xml');
//
//        if ($response->successful()) {
//            $xml = simplexml_load_string($response->body());
//
//            $dolar_buy = $xml->Currency[0]->BanknoteBuying;
//            $dolar_sell = $xml->Currency[0]->BanknoteSelling;
//            $euro_buy = $xml->Currency[3]->BanknoteBuying;
//            $euro_sell = $xml->Currency[3]->BanknoteSelling;
//            $sterlin_buy = $xml->Currency[4]->BanknoteBuying;
//            $sterlin_sell = $xml->Currency[4]->BanknoteSelling;
//
//            return response()->json([
//                'usd' => [
//                    'buy' => $dolar_buy,
//                    'sell' => $dolar_sell,
//                ],
//                'eur' => [
//                    'buy' => $euro_buy,
//                    'sell' => $euro_sell,
//                ],
//                'gbp' => [
//                    'buy' => $sterlin_buy,
//                    'sell' => $sterlin_sell,
//                ],
//            ]);
//        }
//
//        return response()->json(['error' => 'Could not retrieve data'], 500);
//    }
```

<hr>

<h3>Array map ve foreach kullanımı </h3>

```
            $products = $response['data']['S_products']['edges'];

//            $productData = array_map(function ($product) {
//                return [
//                    'id' => $product['node']['id'],
//                    'name' => $product['node']['name'],
//                ];
//            }, $products);

            $productData = [];
            foreach ($products as $product) {
                $productData[] = [
                    'id' => $product['node']['id'],
                    'name' => $product['node']['name'],
                ];
            }
```

<hr>

## not

Game stok güncellemsinin  çalışma mantığı ;

Customer için Orderı oluşturma ne demek  
=> ona ait keyleri reserveye almak
=>  	UpdateStok yapmak zorundayız job tetikle

Aynı customer orderı status güncellmek ne demek
=> Key statusu belirle => Ordırı created durumuna almak reserveye almak ,
Ordırı decline veya reject durumuna alamak keyi active durumuna almak demek

____________________________________________________________________________________________


Yapabildiklerimiz ;

Customer order'ı oluşturuyoruz, oluşturduğumuz oyuna ait stok güncellemesi apilerde yapabiliyoruz eneba hariç.
Customer order'ınnı sadece iptal ettiğimizde, order'a ait oyunlara apiler için stok güncellemesi atabiliyoruz.

Bu Customer order tarafından bizim için yeterli şimdi api servislerine bakacağız.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
