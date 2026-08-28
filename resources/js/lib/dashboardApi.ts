import useSWR from 'swr';
import type {
    JadwalServiceResponse,
    KonsumsiBbmResponse,
    RiwayatPemakaianResponse,
} from '@/types/dashboard';

const fetcher = (url: string) => fetch(url).then(async (response) => {
    if (!response.ok) {
        throw new Error(`Permintaan data dashboard gagal (${response.status})`);
    }

    return response.json();
});

const buildUrl = (path: string, build: (params: URLSearchParams) => void) => {
    const params = new URLSearchParams();
    build(params);

    return `/api/dashboard/${path}?${params.toString()}`;
};

export function useKonsumsiBbm(params: {
    kendaraanIds: number[] | null;
    kategori: 'bulanan' | 'mingguan';
    tahun: number;
    bulan: number | null;
}) {
    const url = params.kendaraanIds
        ? buildUrl('konsumsi-bbm', (search) => {
            params.kendaraanIds?.forEach((id) => search.append('kendaraan_ids[]', String(id)));
            search.set('kategori', params.kategori);
            search.set('tahun', String(params.tahun));

            if (params.bulan !== null) {
                search.set('bulan', String(params.bulan));
            }
        })
        : null;

    return useSWR<KonsumsiBbmResponse | null>(url, url ? fetcher : null);
}

export function useRiwayatPemakaian(idKendaraan: number | null, tahun: number) {
    const url = idKendaraan !== null
        ? buildUrl('riwayat-pemakaian', (search) => {
            search.set('id_kendaraan', String(idKendaraan));
            search.set('tahun', String(tahun));
        })
        : null;

    return useSWR<RiwayatPemakaianResponse | null>(url, url ? fetcher : null);
}

export function useJadwalService(idKendaraan: number | null, bulan: number, tahun: number) {
    const url = idKendaraan !== null
        ? buildUrl('jadwal-service', (search) => {
            search.set('id_kendaraan', String(idKendaraan));
            search.set('bulan', String(bulan));
            search.set('tahun', String(tahun));
        })
        : null;

    return useSWR<JadwalServiceResponse | null>(url, url ? fetcher : null);
}