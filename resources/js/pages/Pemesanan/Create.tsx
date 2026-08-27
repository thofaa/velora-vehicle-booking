import AppLayout from '@/components/AppLayout';
import type { DriverOption } from '@/components/DriverSelect';
import type { KendaraanOption } from '@/components/KendaraanSelect';
import PemesananForm from '@/components/PemesananForm';
import type { PenyetujuOption } from '@/components/PenyetujuLevelSelect';

export default function Create({
    kendaraan,
    driver,
    penyetuju,
}: {
    kendaraan: KendaraanOption[];
    driver: DriverOption[];
    penyetuju: PenyetujuOption[];
}) {
    return (
        <AppLayout title="Buat Pemesanan">
            <div className="max-w-2xl rounded-2xl border bg-white p-6 shadow-sm">
                <PemesananForm kendaraan={kendaraan} driver={driver} penyetuju={penyetuju} />
            </div>
        </AppLayout>
    );
}
