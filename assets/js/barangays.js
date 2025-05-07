const barangaysByCity = {
    'Maasin City': [
        'Abgao', 'Asuncion', 'Baguio District', 'Bahay', 'Banday', 'Bantawan',
        'Basak', 'Batuan', 'Bilibol', 'Cabadiangan', 'Cabulihan', 'Cagnituan',
        'Cambooc', 'Canturing', 'Combado', 'Dongon', 'Guadalupe', 'Hanginan',
        'Ibarra', 'Isagani', 'Labrador', 'Lib-og', 'Lonoy', 'Lunas',
        'Mabini', 'Mahayahay', 'Maintinop', 'Mamingao', 'Maria Clara', 'Nasaug',
        'Nonok Norte', 'Nonok Sur', 'Panan-awan', 'Pasay', 'San Agustin', 'San Francisco',
        'San Isidro', 'San Jose', 'San Rafael', 'Santa Cruz', 'Santa Rosa', 'Soro-soro',
        'Tagnipa', 'Tam-is', 'Tomoy-tomoy', 'Tunga-tunga'
    ],
    'Macrohon': [
        'Aguinaldo', 'Amparo', 'Bagong Silang', 'Buscayan', 'Cambaro', 'Flordeliz',
        'Ichon', 'Ilihan', 'Lapu-lapu', 'Lower Villa Jacinta', 'Maujo', 'Molopolo',
        'Rizal', 'San Isidro', 'San Joaquin', 'San Roque', 'Santa Cruz', 'Upper Villa Jacinta'
    ],
    'Padre Burgos': [
        'Buenavista', 'La Purisima Concepcion', 'Lungsodaan', 'San Juan',
        'Santo Rosario'
    ],
    'Liloan': [
        'Asuncion', 'Caligangan', 'Candayuman', 'Cata-ata', 'Estela',
        'Fatima', 'Himayangan', 'Magbagacay', 'Pres. Quezon', 'Pres. Roxas',
        'San Isidro', 'San Roque', 'Tabugon'
    ],
    'Sogod': [
        'Buac', 'Consolacion', 'Hibod-hibod', 'Hipantag', 'Javier', 'Kahupian', 'Kanangkaan',
        'Kauswagan', 'La Paz', 'Libas', 'Magatas', 'Malinao', 'Maria Plana', 'Milagroso',
        'Olisihan', 'Pancho Villa', 'Poblacion', 'Rizal', 'San Francisco', 'San Isidro',
        'San Jose', 'San Juan', 'San Miguel', 'San Pedro', 'San Roque', 'San Vicente',
        'Santa Maria', 'Suba', 'Tampoong', 'Zone I', 'Zone II', 'Zone III'
    ],
    'Bontoc': [
        'Anahawan', 'Beniton', 'Bocawe', 'Canlupao', 'Casao', 'Divisoria', 'Himakilo',
        'Hilaan', 'Lawgawan', 'Mahayahay', 'Paku', 'Poblacion', 'San Vicente', 'Taa',
        'Union'
    ],
    'Saint Bernard': [
        'Ayahag', 'Bantawon', 'Cabaan', 'Cahumpan', 'Carnaga', 'Catmon', 'Guinsaugon',
        'Himatagon', 'Hindag-an', 'Hinagtikan', 'Kauswagan', 'Lipanto', 'Magbagakay',
        'Mahayag', 'Malinao', 'Nueva Esperanza', 'Poblacion', 'San Isidro', 'Sug-angon',
        'Tabontabon'
    ],
    'San Juan': [
        'Agbao', 'Basak', 'Bobon A', 'Bobon B', 'Bothoan', 'Bugho', 'Canturing',
        'Garrido', 'Minoyho', 'Pong-oy', 'San Jose', 'San Vicente', 'Santa Cruz',
        'Santo Niño', 'Sogod'
    ],
    'Silago': [
        'Badiangon', 'Balagawan', 'Catmon', 'Hingatungan', 'Poblacion', 'Salvacion',
        'Mercedes', 'Sudmon', 'Talisay', 'Tubod'
    ],
    'Hinunangan': [
        'Amparo', 'Bangcas A', 'Bangcas B', 'Biasong', 'Calag-itan', 'District I',
        'District II', 'District III', 'District IV', 'Ilaya', 'Ingan', 'Lungsodaan',
        'Nava', 'Nueva Esperanza', 'Palongpong', 'Poblacion', 'Sagbok', 'San Isidro',
        'San Juan', 'Song-on', 'Talisay', 'Tahusan'
    ],
    'Hinundayan': [
        'Ambao', 'Sagbok', 'Poblacion', 'Baculod', 'Cabulisan', 'Ingan', 'Lawgawan',
        'Linao', 'Manalog', 'Plaridel', 'San Roque', 'Salog'
    ],
    'Anahawan': [
        'Amagusan', 'Calintaan', 'Canlabian', 'Cogon', 'Poblacion', 'San Vicente',
        'Tagup-on'
    ],
    'San Francisco': [
        'Anislagon', 'Bongbong', 'Central', 'Habay', 'Marayag', 'Pinamudlan',
        'Santa Cruz', 'Tinaan'
    ],
    'Pintuyan': [
        'Balongbalong', 'Buenavista', 'Poblacion', 'Mainit', 'Manglit', 'San Roque',
        'Santo Rosario'
    ],
    'San Ricardo': [
        'Benit', 'Bitoon', 'Cabutan', 'Esperanza', 'Looc', 'Poblacion', 'San Antonio',
        'San Ramon', 'Timba'
    ]
};

const defaultAddress = {
    region: 'Region VIII (Eastern Visayas)',
    province: 'Southern Leyte'
};

// Add postal code data
const postalCodes = {
    'Maasin City': '6600',
    'Macrohon': '6601',
    'Padre Burgos': '6602',
    'Liloan': '6603',
    'Sogod': '6604',
    'Bontoc': '6604',
    'Saint Bernard': '6610',
    'San Juan': '6611',
    'Silago': '6606',
    'Hinunangan': '6608',
    'Hinundayan': '6609',
    'Anahawan': '6610',
    'San Francisco': '6611',
    'Pintuyan': '6612',
    'San Ricardo': '6613'
};