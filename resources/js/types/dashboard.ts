export type KategoriKonsumsi = 'bulanan' | 'mingguan';

export interface KendaraanRingkas {
    id: number;
    nomor_polisi: string;
    merk: string;
    tipe: string;
}

export interface KonsumsiBbmResponse {
    kategori: KategoriKonsumsi;
    tahun: number;
    bulan: number | null;
    kendaraan: KendaraanRingkas[];
    data: Array<Record<string, string | number>>;
}

export interface HariPemakaian {
    tanggal: string;
    dipakai: boolean;
    id_pemesanan: number | null;
}

export interface RiwayatPemakaianResponse {
    tahun: number;
    kendaraan: KendaraanRingkas | null;
    hari: HariPemakaian[];
}

export interface JadwalServiceItem {
    id: number;
    tanggal: string;
    jenis: string;
    status: 'terjadwal' | 'selesai' | 'terlewat';
}

export interface JadwalServiceResponse {
    id_kendaraan: number;
    bulan: number;
    tahun: number;
    hari_ini: string;
    kendaraan: KendaraanRingkas | null;
    jadwal: JadwalServiceItem[];
}