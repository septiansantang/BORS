#include <iostream>
#include <cmath>
using namespace std;

// Fungsi untuk menghitung kombinasi (nCr)
double kombinasi(int n, int r) {
    double hasil = 1;
    for (int i = 0; i < r; i++) {
        hasil *= (n - i);
        hasil /= (i + 1);
    }
    return hasil;
}

// Fungsi untuk menghitung probabilitas binomial
double binomial(int n, int x, double p) {
    double q = 1 - p;
    return kombinasi(n, x) * pow(p, x) * pow(q, n - x);
}

int main() {
    int n = 15; // Jumlah orang
    double p = 0.4; // Probabilitas sembuh

    // Paling sedikit 10 orang yang selamat
    double prob_10_or_more = 0;
    for (int x = 10; x <= n; x++) {
        prob_10_or_more += binomial(n, x, p);
    }
    cout << "Probabilitas paling sedikit 10 orang yang selamat: " << prob_10_or_more << endl;

    // Dari 3 sampai 8 orang yang selamat
    double prob_3_to_8 = 0;
    for (int x = 3; x <= 8; x++) {
        prob_3_to_8 += binomial(n, x, p);
    }
    cout << "Probabilitas dari 3 sampai 8 orang yang selamat: " << prob_3_to_8 << endl;

    // Tepat 5 orang yang selamat
    double prob_exactly_5 = binomial(n, 5, p);
    cout << "Probabilitas tepat 5 orang yang selamat: " << prob_exactly_5 << endl;

    // Hitung rata-rata dan variansi
    double mean = n * p;
    double variance = n * p * (1 - p);
    cout << "Rata-rata: " << mean << endl;
    cout << "Variansi: " << variance << endl;

    return 0;
}