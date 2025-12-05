// src/services/neo.rs
use std::time::Duration;

use serde_json::Value;

use crate::state::AppState;
use crate::utils::write_cache;

pub async fn fetch_neo_feed(st: &AppState) -> anyhow::Result<()> {
    let today = chrono::Utc::now().date_naive();
    let start = today - chrono::Days::new(2);
    let url = "https://api.nasa.gov/neo/rest/v1/feed";
    let client = reqwest::Client::builder()
        .timeout(Duration::from_secs(30))
        .build()?;
    let mut req = client.get(url).query(&[
        ("start_date", start.to_string()),
        ("end_date", today.to_string()),
    ]);
    if !st.nasa_key.is_empty() {
        req = req.query(&[("api_key", &st.nasa_key)]);
    }
    let json: Value = req.send().await?.json().await?;
    write_cache(&st.pool, "neo", json).await
}
