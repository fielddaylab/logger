args <- commandArgs(TRUE)
table <- paste("../logger-data/numLevels/numLevelDataForR_", args[1], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 1) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

numLevelsData <- read.table(table, sep=",", header=TRUE)
fit <- lm(formula, data=numLevelsData)
summary(fit)