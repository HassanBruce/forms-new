<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Demo agency matching the Figma designs. Every login uses the password "password". */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $co = Company::create(['name' => 'Tripdesk Demo Holdings', 'slug' => 'demo', 'currency' => 'GBP', 'booking_prefix' => 'TD', 'next_booking_no' => 10400, 'trial_ends_at' => now()->addDays(14)]);
        $mk = fn (string $cls, array $a) => tap(new $cls($a), function ($m) use ($co) { $m->company_id = $co->id; $m->save(); });

        $aln = $mk(Brand::class, ['name' => 'Al-Noor Travel', 'code' => 'ALN', 'color' => '#16A34A', 'email' => 'bookings@alnoortravel.co.uk', 'phone' => '+44 20 7946 0321',
            'address' => '221 Whitechapel Rd, London E1 1DE', 'registration' => 'ATOL 12345 · Company No. 09876543',
            'bank_details' => "Al-Noor Travel Ltd · Barclays\nSort code 20-00-00 · Account 12345678",
            'invoice_terms' => "Balance due 30 days before departure. Prices are per person in GBP. Hotel & visa subject to availability."]);
        $hmt = $mk(Brand::class, ['name' => 'Haramain Tours', 'code' => 'HMT', 'color' => '#0284C7', 'email' => 'hello@haramaintours.co.uk', 'address' => '14 Stratford Rd, Birmingham B11 1AA',
            'bank_details' => "Haramain Tours Ltd · HSBC\nSort code 40-11-22 · Account 87654321"]);

        $user = fn ($name, $email, $role, $brand = null, $team = null) => User::create(['company_id' => $co->id, 'brand_id' => $brand?->id, 'team_id' => $team?->id, 'name' => $name, 'email' => $email, 'role' => $role, 'password' => 'password']);
        $user('Demo Admin', 'admin@demo.test', 'admin', $aln);
        $user('Rabia Noor', 'accounts@demo.test', 'accounts');
        $user('Omar Farooq', 'director@demo.test', 'director', $aln);
        $user('Nadia Aslam', 'nadia@demo.test', 'director', $hmt);

        $tA = $mk(Team::class, ['brand_id' => $aln->id, 'name' => 'Team Ahmed']);
        $tB = $mk(Team::class, ['brand_id' => $aln->id, 'name' => 'Team Bilal']);
        $tH = $mk(Team::class, ['brand_id' => $hmt->id, 'name' => 'Team Hina']);
        $tA->update(['lead_id' => $user('Ahmed Raza', 'lead@demo.test', 'team_lead', $aln, $tA)->id]);
        $tB->update(['lead_id' => $user('Bilal Ahmed', 'bilal@demo.test', 'team_lead', $aln, $tB)->id]);
        $tH->update(['lead_id' => $user('Hina Javed', 'hina@demo.test', 'team_lead', $hmt, $tH)->id]);
        $sara = $user('Sara Khan', 'agent@demo.test', 'agent', $aln, $tA);
        $usman = $user('Usman Tariq', 'usman@demo.test', 'agent', $aln, $tA);
        $zain = $user('Zain Ali', 'zain@demo.test', 'agent', $aln, $tB);
        $faraz = $user('Faraz Hussain', 'faraz@demo.test', 'agent', $hmt, $tH);

        foreach ([['Sabre GDS', 'flight'], ['Saudia Direct', 'flight'], ['Hotelbeds', 'hotel'], ['WebBeds', 'hotel'], ['Al-Rahma Transport', 'transport'], ['Taiba Transport', 'transport'], ['Al-Masa Visa Services', 'visa']] as [$n, $t]) {
            $mk(Supplier::class, ['name' => $n, 'type' => $t]);
        }

        // The exact booking from the hand-drawn spec: 2 adults × 100 + 3 children × 70 = 410 GBP.
        $b = $this->booking($co, $aln, $sara, 'TD-10482', 'Ali Haider', 'accepted', now()->subDays(2), [
            'profile_no' => 'PRF-2291', 'email' => 'ali.haider@example.com', 'phone' => '+44 7700 900123', 'pnr' => '6KQ2LM', 'airline' => 'Saudia (SV)', 'flight_supplier' => 'Sabre GDS',
            'flight_details' => "1  SV 125  Y  12NOV  LHRJED  HK5   2130  0640+1\n2  SV 1462 Y  20NOV  JEDMED  HK5   1100  1205\n3  SV 126  Y  26NOV  MEDLHR  HK5   1415  1910\nBaggage 2PC · Meals included",
        ]);
        foreach ([['Mr.', 'Ali', 'adult'], ['Mrs.', 'Sana', 'adult'], ['Mstr.', 'Ahmed', 'child'], ['Miss', 'Zara', 'child'], ['Mstr.', 'Musa', 'child']] as $i => [$s, $f, $t]) {
            $b->passengers()->create(['salutation' => $s, 'first_name' => $f, 'surname' => 'Haider', 'type' => $t, 'position' => $i]);
        }
        $b->hotels()->create(['city' => 'Makkah', 'hotel_name' => 'Swissôtel Al Maqam', 'room_type' => 'Quad', 'rooms' => 2, 'meal_type' => 'Breakfast', 'view_type' => 'Haram view', 'check_in' => '2026-11-12', 'check_out' => '2026-11-20', 'supplier' => 'Hotelbeds', 'ref_no' => 'HB-77812', 'hcn' => 'MK-559201']);
        $b->hotels()->create(['city' => 'Madinah', 'hotel_name' => 'Pullman Zamzam', 'room_type' => 'Quint', 'rooms' => 1, 'meal_type' => 'Half board', 'view_type' => 'City view', 'check_in' => '2026-11-20', 'check_out' => '2026-11-26', 'supplier' => 'WebBeds', 'ref_no' => 'WB-33190', 'hcn' => 'MD-108844']);
        foreach ([['GMC Yukon', 7, 'Jeddah Airport → Makkah Hotel', 'Al-Rahma Transport'], ['GMC Yukon', 7, 'Makkah Hotel → Madinah Hotel', 'Al-Rahma Transport'], ['H1 Van', 10, 'Madinah Hotel → Madinah Airport', 'Taiba Transport']] as [$v, $c, $r, $s]) {
            $b->transports()->create(['vehicle_type' => $v, 'capacity' => $c, 'route' => $r, 'supplier' => $s]);
        }
        $b->visas()->create(['visa_type' => 'Umrah visa (90 days)', 'supplier' => 'Al-Masa Visa Services', 'quantity' => 5]);
        $b->prices()->create(['pax_type' => 'Adult', 'quantity' => 2, 'unit_price' => 100]);
        $b->prices()->create(['pax_type' => 'Child', 'quantity' => 3, 'unit_price' => 70]);
        foreach ([['flight', 'Adult flight', 2, 45], ['flight', 'Child flight', 3, 20], ['hotel', 'Makkah · Swissôtel Al Maqam', 8, 9], ['hotel', 'Madinah · Pullman Zamzam', 6, 8], ['transport', 'GMC Yukon (3 sectors)', 1, 35], ['visa', 'Umrah visa', 5, 5]] as [$cat, $d, $q, $u]) {
            $b->costs()->create(['category' => $cat, 'description' => $d, 'quantity' => $q, 'unit_cost' => $u]);
        }
        $this->pay($b, '2026-10-14', 'BARC-88213', 'bank_transfer', 100);
        $this->pay($b, '2026-10-16', 'CASH-0091', 'cash', 50);
        $b->syncTravelDates();
        $b->recalculate();
        $b->update(['step' => 5]);

        // History for the dashboard chart and lists.
        $names = ['Fatima Malik', 'Imran Qureshi', 'Zainab Akhtar', 'Ayesha Siddiqui', 'Hamza Shah', 'Noor Fatima', 'Yusuf Rahman', 'Bilal Chaudhry', 'Mariam Iqbal', 'Tariq Mehmood', 'Hafsa Butt', 'Kamran Sheikh', 'Sadia Rehman', 'Adnan Malik', 'Rizwan Akram', 'Amna Javed', 'Shahid Latif', 'Iqra Nadeem', 'Usama Saeed', 'Khadija Anwar', 'Faisal Raza', 'Hira Aslam', 'Saad Qamar', 'Mehwish Tahir'];
        $agents = [[$aln, $sara], [$aln, $usman], [$aln, $zain], [$hmt, $faraz]];
        mt_srand(42);
        foreach ($names as $i => $name) {
            [$brand, $agent] = $agents[$i % 4];
            $created = now()->subDays(mt_rand(0, 330))->setTime(mt_rand(9, 18), mt_rand(0, 59));
            if ($i < 6) $created = now()->subDays(mt_rand(0, 12));
            $status = ['accepted', 'accepted', 'accepted', 'waiting', 'rejected', 'accepted', 'waiting'][$i % 7];
            $adults = mt_rand(1, 4); $kids = mt_rand(0, 3);
            $start = $created->copy()->addDays(mt_rand(20, 70));
            $bk = $this->booking($co, $brand, $agent, 'TD-'.(10400 + $i), $name, $status, $created, [
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com', 'pnr' => strtoupper(substr(md5($name), 0, 6)),
                'travel_start' => $start, 'travel_end' => $start->copy()->addDays(mt_rand(7, 21)), 'step' => 5,
            ]);
            [$first, $last] = explode(' ', $name);
            $bk->passengers()->create(['salutation' => 'Mr.', 'first_name' => $first, 'surname' => $last, 'type' => 'adult', 'position' => 0]);
            for ($p = 1; $p < $adults + $kids; $p++) $bk->passengers()->create(['salutation' => $p < $adults ? 'Mrs.' : 'Mstr.', 'first_name' => 'Guest '.$p, 'surname' => $last, 'type' => $p < $adults ? 'adult' : 'child', 'position' => $p]);
            $ap = mt_rand(55, 140) * 10; $cp = (int) round($ap * 0.7);
            $bk->prices()->create(['pax_type' => 'Adult', 'quantity' => $adults, 'unit_price' => $ap]);
            if ($kids) $bk->prices()->create(['pax_type' => 'Child', 'quantity' => $kids, 'unit_price' => $cp]);
            $sale = $adults * $ap + $kids * $cp;
            $bk->costs()->create(['category' => 'flight', 'description' => 'Return flights', 'quantity' => $adults + $kids, 'unit_cost' => round($sale * 0.45 / ($adults + $kids), 2)]);
            $bk->costs()->create(['category' => 'hotel', 'description' => 'Makkah + Madinah hotels', 'quantity' => 1, 'unit_cost' => round($sale * mt_rand(25, 33) / 100, 2)]);
            $bk->costs()->create(['category' => 'visa', 'description' => 'Umrah visa', 'quantity' => $adults + $kids, 'unit_cost' => 25]);
            if ($status === 'accepted') {
                $paid = [1, 0.5, 0.3, 1, 0.6][$i % 5];
                $this->pay($bk, $created->copy()->addDays(2)->format('Y-m-d'), 'BANK-'.mt_rand(10000, 99999), 'bank_transfer', round($sale * $paid, 2));
            }
            $bk->recalculate();
        }
        $co->update(['next_booking_no' => 10483, 'next_invoice_no' => 1]);
    }

    private function booking(Company $co, Brand $brand, User $agent, string $ref, string $lead, string $status, Carbon $created, array $extra = []): Booking
    {
        $b = new Booking(array_merge(['brand_id' => $brand->id, 'team_id' => $agent->team_id, 'agent_id' => $agent->id, 'lead_name' => $lead, 'status' => $status, 'currency' => $co->currency, 'step' => 5], $extra));
        $b->company_id = $co->id;
        $b->ref = $ref;
        $b->created_at = $created;
        $b->updated_at = $created;
        $b->save();
        return $b;
    }

    private function pay(Booking $b, string $on, string $ref, string $method, float $amount): void
    {
        $p = new Payment(['paid_on' => $on, 'reference' => $ref, 'method' => $method, 'amount' => $amount]);
        $p->booking_id = $b->id;
        $p->save();
    }
}
