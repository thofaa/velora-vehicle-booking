export type PersetujuanRecord = {
    id: number;
    level_persetujuan: number;
    status: 'pending' | 'approved' | 'rejected' | 'dibatalkan';
    approved_at?: string | null;
    catatan?: string | null;
    id_pihak_penyetuju: number;
    penyetuju?: { id: number; name: string } | null;
    pemesanan?: PemesananRecord | null;
    level_sebelumnya?: { level: number; status: string }[] | null;
};

export type PemesananRecord = {
    id: number;
    tanggal_mulai: string;
    tanggal_selesai: string;
    status: 'menunggu_persetujuan' | 'disetujui' | 'ditolak' | 'dibatalkan';
    kendaraan?: {
        nomor_polisi: string;
        merk: string;
        tipe: string;
    } | null;
    driver?: { nama: string; nomor_telepon: string } | null;
    admin?: { id: number; name: string } | null;
    persetujuan?: PersetujuanRecord[];
    created_at?: string;
};

export const STATUS_LABEL: Record<string, string> = {
    menunggu_persetujuan: 'Menunggu',
    disetujui: 'Disetujui',
    ditolak: 'Ditolak',
    dibatalkan: 'Dibatalkan',
    pending: 'Pending',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    dibatalkan_persetujuan: 'Dibatalkan',
};

export const STATUS_BADGE: Record<string, string> = {
    menunggu_persetujuan: 'bg-amber-100 text-amber-700',
    disetujui: 'bg-emerald-100 text-emerald-700',
    ditolak: 'bg-red-100 text-red-700',
    dibatalkan: 'bg-gray-100 text-gray-600',
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
};
