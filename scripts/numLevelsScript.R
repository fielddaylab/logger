library(rjson)
args <- commandArgs(TRUE)
table <- paste(fromJSON(file="config.json", method="C")$DATA_DIR, "/", args[2], "/numLevels/numLevelsData_", args[1], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 2) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

numLevelsData <- read.table(table, sep=",", header=TRUE)
fit <- lm(formula, data=numLevelsData)
summary(fit)