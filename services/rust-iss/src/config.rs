use std::time::Duration;

#[derive(Clone, Debug)]
pub struct AppConfig {
    pub database_url: String,
    pub nasa_url: String,      // OSDR API
    pub nasa_key: String,      // ключ NASA
    pub fallback_url: String,  // where-the-iss
    pub every_osdr: Duration,
    pub every_iss: Duration,
    pub every_apod: Duration,
    pub every_neo: Duration,
    pub every_donki: Duration,
    pub every_spacex: Duration,
}

impl AppConfig {
    pub fn from_env() -> anyhow::Result<Self> {
        let database_url = std::env::var("DATABASE_URL")
            .or_else(|_| std::env::var("DB_URL"))?; // как и было в main

        let nasa_url = std::env::var("OSDR_DATASETS_URL")
            .unwrap_or_else(|_| {
                "https://visualization.osdr.nasa.gov/biodata/api/v2/datasets/?format=json"
                    .to_string()
            });

        let nasa_key = std::env::var("NASA_API_KEY").unwrap_or_default();

        let fallback_url = std::env::var("WHERE_ISS_URL")
            .unwrap_or_else(|_| "https://api.wheretheiss.at/v1/satellites/25544".to_string());

        Ok(Self {
            database_url,
            nasa_url,
            nasa_key,
            fallback_url,
            every_osdr:   env_duration("FETCH_EVERY_SECONDS", 600),
            every_iss:    env_duration("ISS_EVERY_SECONDS",   120),
            every_apod:   env_duration("APOD_EVERY_SECONDS",  43200),
            every_neo:    env_duration("NEO_EVERY_SECONDS",   7200),
            every_donki:  env_duration("DONKI_EVERY_SECONDS", 3600),
            every_spacex: env_duration("SPACEX_EVERY_SECONDS",3600),
        })
    }
}

fn env_duration(key: &str, default_secs: u64) -> Duration {
    let raw = std::env::var(key).ok();
    let secs = raw
        .as_deref()
        .and_then(|s| s.parse::<u64>().ok())
        .unwrap_or(default_secs);
    Duration::from_secs(secs)
}
