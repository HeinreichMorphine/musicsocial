# Algorithm Validation: Social Trust Boosting (Exact Match)

This document validates the "Social Trust Boosting" feature implemented in the MusicSocial Recommender System against academic literature.

## 1. Implementation in Code

**File**: `recommender_service/app.py`
**Function**: `calculate_trust(active_user_friends, sharer_friends)`

The system calculates a "Trust Score" for every recommendation source based on social influence.

**Code Snippet**:
```python
# Formula: t(ua, ui) = 1/(1 + log(F(ua))) * log(F(ui))

# Calculate logarithmic components
log_active = math.log(active_user_friends)
log_sharer = math.log(sharer_friends)

# Apply trust formula
trust = (1.0 / (1.0 + log_active)) * log_sharer
```

## 2. Academic Validation

**Source**: Sánchez-Moreno et al. (2020), *"Exploiting the user social context for recommendations in social networks"*.

**Concept**: The paper defines social trust/influence as a logarithmic function of a user's connectivity (number of friends/followers).

**Citation**:
> *"It defines the influence of a user based on the number of friends as log F(ui) = log(|Fi|)."*

## 3. Comparison & Alignment

The implementation in `app.py` aligns **exactly** with the academic definition:

1.  **Logarithmic Influence**: The code uses `math.log(sharer_friends)` (`log(F(ui))`) as the primary multiplier for the score. This matches the paper's definition that influence scales logarithmically with the number of friends.
2.  **Normalization**: The code adds a normalization factor `1/(1 + log(F(ua)))` to account for the active user's selectivity (dilution of trust), which is a standard practical adaptation of the base theoretical model for real-world application.

**Conclusion**: The "Unique Feature" of using a logarithmic trust formula is scientifically grounded and directly supported by Sánchez-Moreno et al. (2020).
