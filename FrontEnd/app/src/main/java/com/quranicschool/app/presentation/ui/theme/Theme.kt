package com.quranicschool.app.presentation.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val LightColorScheme = lightColorScheme(
    primary = BrownPrimary,
    onPrimary = White,
    secondary = BrownLight,
    onSecondary = White,
    tertiary = BrownAccent,
    background = CreamLight,
    onBackground = TextDark,
    surface = White,
    onSurface = TextDark,
    surfaceVariant = FieldBackground,
    onSurfaceVariant = TextGray,
    outline = FieldBorder
)

@Composable
fun QuranAppTheme(
    content: @Composable () -> Unit
) {
    MaterialTheme(
        colorScheme = LightColorScheme,
        typography = Typography,
        content = content
    )
}