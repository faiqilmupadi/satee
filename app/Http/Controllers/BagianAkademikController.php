<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\RuangPerkuliahan;
use App\Models\PengalokasianRuang;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;

class BagianAkademikController extends Controller
{
    public function indexPenyusunanRuang()
    {
        $ruangPerkuliahan = RuangPerkuliahan::all(); // Mengambil semua data ruang perkuliahan
        return view('bagianakademik.penyusunanruang.index', compact('ruangPerkuliahan'));
    }
    public function indexPengalokasianRuang()
    {
        $alokasiRuang = PengalokasianRuang::all();

        return view('bagianakademik.lihatpengalokasianruang', compact('alokasiRuang'));
    }
    public function createPenyusunanRuang()
    {
        return view('bagianakademik.penyusunanruang.create');
    }

    // Menampilkan form penyusunan ruang
    public function createPengalokasianRuang()
    {
        // Mengambil data dari tabel ruangperkuliahan dan program_studi
        $ruangPerkuliahan = RuangPerkuliahan::all();
        $programStudi = ProgramStudi::all();

        return view('bagianakademik.pengalokasianruang', compact('ruangPerkuliahan', 'programStudi'));
    }
    // Method untuk menyimpan data
    public function storePenyusunanRuang(Request $request)
    {

        Session::flash('kode_ruang', $request->kode_ruang);
        Session::flash('gedung', $request->gedung);
        Session::flash('kapasitas', $request->kapasitas);

        try {
            // Validasi input
            $validatedData = $request->validate(
                [
                    'kode_ruang' => 'required|string|max:25|unique:ruangperkuliahan,kode_ruang',
                    'gedung' => 'required|string|max:50',
                    'kapasitas' => 'required|integer',
                ],
                [
                    'kode_ruang.required' => 'Kode ruang wajib diisi',
                    'kode_ruang.unique' => 'Kode ruang sudah ada di database',
                    'gedung.required' => 'Gedung wajib diisi',
                    'kapasitas.required' => 'Kapasitas wajib diisi',
                ]
            );
            // Simpan data ke tabel ruangperkuliahan
            RuangPerkuliahan::create([
                'kode_ruang' => $validatedData['kode_ruang'],
                'gedung' => $validatedData['gedung'],
                'kapasitas' => $validatedData['kapasitas'],
            ]);

            // Redirect setelah sukses
            return redirect()->route('penyusunanruang.create')->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    // Menyimpan data pengalokasian ruang ke tabel
    public function storePengalokasianRuang(Request $request)
    {
        Session::flash('kode_ruang', $request->kode_ruang);
        Session::flash('id_programstudi', $request->id_programstudi);

        try {
            $validatedData = $request->validate(
                [
                    'kode_ruang' => 'required|string|exists:ruangperkuliahan,kode_ruang',
                    'id_programstudi' => 'required|integer|exists:program_studi,id_programstudi',
                ],
                [
                    'kode_ruang.required' => 'Kode ruang wajib diisi',
                    'id_programstudi.required' => 'Program studi wajib diisi',
                ]
            );

            // Cek apakah ada alokasi ruang yang sudah ada dengan status 'disetujui' atau 'menunggu konfirmasi'
            $existingPengajuan = PengalokasianRuang::where('kode_ruang', $validatedData['kode_ruang'])
                ->whereIn('status', ['disetujui', 'menunggu konfirmasi'])
                ->first();

            if ($existingPengajuan) {
                // Jika sudah ada pengajuan, berikan pesan error
                return redirect()->back()->withErrors(['error' => 'Alokasi ruang sudah pernah diajukan dengan status ' . $existingPengajuan->status])->withInput();
            }
            // Menyimpan data ke tabel pengalokasianruang
            PengalokasianRuang::create([
                'kode_ruang' => $validatedData['kode_ruang'],
                'id_programstudi' => $validatedData['id_programstudi'],
            ]);

            return redirect()->route('pengalokasianruang.create')->with('success', 'Pengalokasian ruang telah diajukan ke dekan.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function editPenyusunanRuang(string $kode_ruang)
    {
        $ruangPerkuliahan = RuangPerkuliahan::findOrFail($kode_ruang);
        return view('bagianakademik.penyusunanruang.edit', compact('ruangPerkuliahan'));
    }

    public function updatePenyusunanRuang(Request $request, string $kode_ruang)
    {

        $validatedData = $request->validate(
            [
                'gedung' => 'required|string|max:50',
                'kapasitas' => 'required|integer',
            ],
            [
                'gedung.required' => 'Gedung wajib diisi',
                'kapasitas.required' => 'Kapasitas wajib diisi',
            ]
        );
        // Simpan data ke tabel ruangperkuliahan
        $ruangPerkuliahan = ([
            'gedung' => $validatedData['gedung'],
            'kapasitas' => $validatedData['kapasitas'],
        ]);

        RuangPerkuliahan::where('kode_ruang', $kode_ruang)->update($ruangPerkuliahan);
        // Redirect setelah sukses
        return redirect()->route('penyusunanruang.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroyPenyusunanRuang(string $kode_ruang)
    {
        RuangPerkuliahan::where('kode_ruang', $kode_ruang)->delete();
        return redirect()->route('penyusunanruang.index')->with('success', 'Data berhasil dihapus');
    }


}
